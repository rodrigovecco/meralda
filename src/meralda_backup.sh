#!/bin/bash
###############################################################
# Meralda Backup Script
#
# 🗂️  Supports full, incremental, and estimate-only modes
# 📦 Archives all files under `appdata` and `public_html/data`
# 🧠 Always includes a full database dump (regardless of mode)
# 📝 Adds metadata and logs to track backups
# 📌 Usage:
#     bash meralda_backup.sh                   # full backup
#     bash meralda_backup.sh --incremental     # only changed files
#     bash meralda_backup.sh --estimate-only   # only calculate space
#     bash meralda_backup.sh --estimate-only --incremental  # incremental estimation
#
# 🔧 Configurable settings:
#     - PROGRESS_BATCH_SIZE: updates progress every X files
#     - BATCH_SIZE: how many files are added per zip command
#     - SKIP_SIZE_ESTIMATE: disables disk space pre-check
#
# 📄 Creates:
#     - sysbackup/YYYYMMDDHHMMSS-full.zip or -inc.zip
#     - db.sql and info.txt inside the archive
#     - sysbackup/last_successful_backup.txt (timestamp in UTC)
#     - sysbackup/backup.log (summary log)
# 👤 Author: Rodrigo Vecco Haddad
# 📅 Date: 2025-10-13
# 🧾 Version: 1.1.1
# 📓 Changelog:
#     - v1.0.0: Initial release
#     - v1.0.1: Skip drop table, separate database files
#     - v1.1.0: Added --estimate-only and incremental-aware space estimation
#     - v1.1.1: Fixed human-readable size parsing ("233M" error)
###############################################################
trap 'echo -e "\n❌ Backup interrupted by user"; exit 130' INT
VERSION="1.1.1"

for cmd in mysqldump tar gzip du awk df grep find zip unzip mysql; do
  if ! command -v $cmd >/dev/null 2>&1; then
    echo "❌ Required command '$cmd' is not installed. Aborting."
    exit 1
  fi
done

PROGRESS_BATCH_SIZE=100
BATCH_SIZE=1000
SKIP_SIZE_ESTIMATE=false

CHARESC="\033"
CHARNLR="\r"
CHARNLN="\n"

ROOT_PATH="."
SRC_PATH="."
LAST_BACKUP_DATE_FILE="$SRC_PATH/sysbackup/last_successful_backup.txt"
LOG_FILE="$SRC_PATH/sysbackup/backup.log"

# ──────────────────────────────────────────────────────────────
# 🔍 Parse arguments
# ──────────────────────────────────────────────────────────────
INCREMENTAL=false
ESTIMATE_ONLY=false

for arg in "$@"; do
  case "$arg" in
    --incremental)
      if [ -f "$LAST_BACKUP_DATE_FILE" ]; then
        INCREMENTAL=true
      else
        echo "⚠️  No previous backup found; running as full backup."
      fi
      ;;
    --estimate-only)
      ESTIMATE_ONLY=true
      ;;
  esac
done

mkdir -p "$(dirname "$LOG_FILE")"
DATETIME=$(date +%Y%m%d%H%M%S)
BACKUP_MODE_SUFFIX=$([ "$INCREMENTAL" = true ] && echo "-inc" || echo "-full")
ARCHIVE_FILE="$SRC_PATH/sysbackup/${DATETIME}${BACKUP_MODE_SUFFIX}.zip"
LOG_DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
echo "[$LOG_DATETIME] START | File: $ARCHIVE_FILE" >> "$LOG_FILE"
START_TIME=$(date +%s)
ARCHIVE_NAME=$(basename "$ARCHIVE_FILE")
ARCHIVE_BASENAME="${ARCHIVE_NAME%.zip}"
CFG_PATH="$SRC_PATH/app/cfg/db.php"
BACKUP_PATH="$SRC_PATH/sysbackup"
BACKUP_TEMP="$BACKUP_PATH/$DATETIME"
SQL_FILE="$BACKUP_TEMP/db.sql"
INFO_FILE="$BACKUP_TEMP/info.txt"
STRUCTURE_FILE="$BACKUP_TEMP/structure.sql"
VIEWS_FILE="$BACKUP_TEMP/views.sql"
DATA_FILE="$BACKUP_TEMP/data.sql"

DB_HOST=$(grep -oP '"host"\s*=>\s*"\K[^"]+' "$CFG_PATH")
DB_NAME=$(grep -oP '"db"\s*=>\s*"\K[^"]+' "$CFG_PATH")
DB_USER=$(grep -oP '"user"\s*=>\s*"\K[^"]+' "$CFG_PATH")
DB_PASS=$(grep -oP '"pass"\s*=>\s*"\K[^"]+' "$CFG_PATH")

mkdir -p "$BACKUP_PATH"

###############################################################
# 🧮 Space estimation (supports full, incremental, and estimate-only)
###############################################################
if [ "$SKIP_SIZE_ESTIMATE" != "true" ]; then
  echo "⏳ Calculating estimated backup size..."

  APPDATA_SIZE=0
  DATA_SIZE=0
  FILES_CHANGED_APPDATA=0
  FILES_CHANGED_DATA=0

  if [ "$INCREMENTAL" = true ] && [ -f "$LAST_BACKUP_DATE_FILE" ]; then
    MOD_SINCE_DATE=$(cat "$LAST_BACKUP_DATE_FILE")

    if [ -d "$SRC_PATH/appdata" ]; then
      FILES_CHANGED_APPDATA=$(find "$SRC_PATH/appdata" -type f -newermt "$MOD_SINCE_DATE" | wc -l)
      APPDATA_SIZE=$(find "$SRC_PATH/appdata" -type f -newermt "$MOD_SINCE_DATE" -print0 \
        | du --files0-from=- -ck 2>/dev/null | tail -n1 | awk '{print $1}')
    fi

    if [ -d "$SRC_PATH/public_html/data" ]; then
      FILES_CHANGED_DATA=$(find "$SRC_PATH/public_html/data" -type f -newermt "$MOD_SINCE_DATE" | wc -l)
      DATA_SIZE=$(find "$SRC_PATH/public_html/data" -type f -newermt "$MOD_SINCE_DATE" -print0 \
        | du --files0-from=- -ck 2>/dev/null | tail -n1 | awk '{print $1}')
    fi
  else
    [ -d "$SRC_PATH/appdata" ] && APPDATA_SIZE=$(du -sk "$SRC_PATH/appdata" | awk '{print $1}')
    [ -d "$SRC_PATH/public_html/data" ] && DATA_SIZE=$(du -sk "$SRC_PATH/public_html/data" | awk '{print $1}')
  fi

  ESTIMATED_TOTAL_KB=$(((APPDATA_SIZE + DATA_SIZE + 10240) * 12 / 10))
  AVAILABLE_SPACE=$(df --output=avail "$BACKUP_PATH" | tail -n 1)

  ESTIMATED_MB=$((ESTIMATED_TOTAL_KB / 1024))
  AVAILABLE_MB=$((AVAILABLE_SPACE / 1024))
  MODE_LABEL=$([ "$INCREMENTAL" = true ] && echo "incremental" || echo "full")

  echo "📦 Estimated total size: ${ESTIMATED_MB} MB (${MODE_LABEL} mode)"
  echo "💾 Available space: ${AVAILABLE_MB} MB"
  if [ "$INCREMENTAL" = true ]; then
    echo "📁 Files changed since last backup: $((FILES_CHANGED_APPDATA + FILES_CHANGED_DATA))"
  fi

  if [ -z "$AVAILABLE_SPACE" ] || [ "$AVAILABLE_SPACE" -lt "$ESTIMATED_TOTAL_KB" ]; then
    echo "❌ Not enough disk space. Required: ${ESTIMATED_MB} MB, Available: ${AVAILABLE_MB} MB"
    echo "[$LOG_DATETIME] ESTIMATE | ❌ Not enough space (${ESTIMATED_MB}MB req / ${AVAILABLE_MB}MB avail)" >> "$LOG_FILE"
    exit 1
  else
    echo "✅ Enough disk space available."
    echo "[$LOG_DATETIME] ESTIMATE | ✅ ${ESTIMATED_MB}MB required, ${AVAILABLE_MB}MB available (${MODE_LABEL} mode)" >> "$LOG_FILE"
  fi

  if [ "$ESTIMATE_ONLY" = true ]; then
    echo "🧮 Estimate-only mode enabled. Exiting without creating backup."
    exit 0
  fi

else
  echo "⚠️  Skipping space estimation as configured (SKIP_SIZE_ESTIMATE=true)"
fi

###############################################################
# 🧱 Database dump
###############################################################
mkdir -p "$BACKUP_TEMP"
TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -Nse "SHOW FULL TABLES IN \`$DB_NAME\` ;")

mysqldump --no-tablespaces --skip-add-drop-table \
  --no-data \
  -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"  \
  --result-file="$STRUCTURE_FILE" 2>/dev/null

mysqldump --no-tablespaces --skip-add-drop-table \
  --no-data --routines --no-create-info --no-create-db --skip-triggers \
  --skip-add-locks --skip-comments \
  -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  --result-file="$VIEWS_FILE" 2>/dev/null

mysqldump --no-tablespaces --skip-add-drop-table \
  --no-create-info \
  --insert-ignore \
  -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  --result-file="$DATA_FILE" 2>/dev/null

###############################################################
# 🧾 Info file generation
###############################################################
{
  echo "Backup Date: $(date)"
  echo "Script Version: $VERSION"
  echo "Database Host: $DB_HOST"
  echo "Database Name: $DB_NAME"
  echo "Database User: $DB_USER"
  echo
  echo "Included folders:"
  echo " - $SRC_PATH/appdata"
  echo " - $SRC_PATH/public_html/data"
  echo
  echo "Execution Start Time: $(date -d @$START_TIME)"
} > "$INFO_FILE"

###############################################################
# 📦 File archiving
###############################################################
LAST_BACKUP_DATE_FILE="$BACKUP_PATH/last_successful_backup.txt"
if [ "$INCREMENTAL" = true ] && [ -f "$LAST_BACKUP_DATE_FILE" ]; then
  MODIFIED_SINCE="-newermt \"$(cat $LAST_BACKUP_DATE_FILE)\""
else
  MODIFIED_SINCE=""
fi

# Add appdata
if [ -d "appdata" ]; then
  TOTAL_FILES=$(find appdata -type f | wc -l)
  if [ "$TOTAL_FILES" -eq 0 ]; then
    echo "⚠️  No files found in appdata, skipping."
  else
    printf "➕ Adding appdata to backup...$CHARNLR$CHARNLN"
    FILE_COUNT=0
    BATCH=""
    while IFS= read -r file; do
      BATCH+="$file$CHARNLN"
      FILE_COUNT=$((FILE_COUNT + 1))
      if (( FILE_COUNT % PROGRESS_BATCH_SIZE == 0 )); then
        PERCENT=$((FILE_COUNT * 100 / TOTAL_FILES))
        printf "$CHARNLR$CHARESC[K  → %s/%s appdata (%s%%)..." "$FILE_COUNT" "$TOTAL_FILES" "$PERCENT"
      fi
      if (( FILE_COUNT % BATCH_SIZE == 0 )); then
        echo -e "$BATCH" | zip -q "$ARCHIVE_FILE" -@
        BATCH=""
      fi
    done < <(eval find appdata -type f $MODIFIED_SINCE)
    [ -n "$BATCH" ] && echo -e "$BATCH" | zip -q "$ARCHIVE_FILE" -@
  fi
fi

# Add public_html/data
if [ -d "public_html/data" ]; then
  TOTAL_FILES=$(find public_html/data -type f | wc -l)
  if [ "$TOTAL_FILES" -eq 0 ]; then
    echo "⚠️  No files found in public_html/data, skipping."
  else
    printf "➕ Adding public_html/data to backup...$CHARNLR$CHARNLN"
    FILE_COUNT=0
    BATCH=""
    while IFS= read -r file; do
      BATCH+="$file$CHARNLN"
      FILE_COUNT=$((FILE_COUNT + 1))
      if (( FILE_COUNT % PROGRESS_BATCH_SIZE == 0 )); then
        PERCENT=$((FILE_COUNT * 100 / TOTAL_FILES))
        printf "$CHARNLR$CHARESC[K  → %s/%s data (%s%%)..." "$FILE_COUNT" "$TOTAL_FILES" "$PERCENT"
      fi
      if (( FILE_COUNT % BATCH_SIZE == 0 )); then
        printf "%s" "$BATCH" | zip -q "$ARCHIVE_FILE" -@
        BATCH=""
      fi
    done < <(eval find public_html/data -type f $MODIFIED_SINCE)
    [ -n "$BATCH" ] && echo -e "$BATCH" | zip -q "$ARCHIVE_FILE" -@
  fi
fi

echo "➕ Adding database to backup..."
zip -j -q "$ARCHIVE_FILE" "$STRUCTURE_FILE" "$VIEWS_FILE" "$DATA_FILE"
zip -j -q "$ARCHIVE_FILE" "$INFO_FILE"

###############################################################
# ✅ Finalize
###############################################################
END_TIME=$(date +%s)
ELAPSED_TIME=$((END_TIME - START_TIME))
ARCHIVE_SIZE=$(du -h "$ARCHIVE_FILE" | awk '{print $1}')
TOTAL_BACKED_UP_FILES=$(unzip -l "$ARCHIVE_FILE" | grep -E '^ +[0-9]+' | wc -l)

if unzip -l "$ARCHIVE_FILE" | grep -q "structure.sql"; then
  echo "✅ Backup complete: $ARCHIVE_FILE ($ARCHIVE_SIZE)"
  echo "[$LOG_DATETIME] DONE  | ✅ Completed in ${ELAPSED_TIME}s, ${ARCHIVE_SIZE}, ${TOTAL_BACKED_UP_FILES} files" >> "$LOG_FILE"
  date -u '+%Y-%m-%d %H:%M:%S UTC' > "$LAST_BACKUP_DATE_FILE"
  echo "⏱️  Execution time: ${ELAPSED_TIME} seconds"
else
  echo "❌ Backup failed: structure.sql not found in archive"
  echo "[$LOG_DATETIME] FAIL  | ❌ structure.sql missing" >> "$LOG_FILE"
  rm -f "$ARCHIVE_FILE"
  exit 2
fi
