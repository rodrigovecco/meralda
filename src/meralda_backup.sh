#!/bin/bash
###############################################################
# Meralda Backup Script
#
# 🗂️  Supports full and incremental backups
# 📦 Archives all files under `appdata` and `public_html/data`
# 🧠 Always includes a full database dump (regardless of mode)
# 📝 Adds metadata and logs to track backups
# 📌 Usage:
#     bash meralda_backup.sh             # full backup
#     bash meralda_backup.sh --incremental  # only changed files (requires timestamp file)
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
# 📅 Date: 2025-04-17
# 🧾 Version: 1.0.1
# 📓 Changelog:
#     - v1.0.0: Initial release with full/incremental backup support, progress tracking, and structured logging.
#     - v1.0.1: Skip drop table, separate database files
###############################################################
# Catch Ctrl+C interruptions
trap 'echo -e "\n❌ Backup interrupted by user"; exit 130' INT
VERSION="1.0.1"
# Check required commands
for cmd in mysqldump tar gzip du awk df grep; do
  if ! command -v $cmd >/dev/null 2>&1; then
    echo "❌ Required command '$cmd' is not installed. Aborting."
    exit 1
  fi
done

# Configurable batch size for progress updates and zip chunks
PROGRESS_BATCH_SIZE=100
BATCH_SIZE=1000

# Allow skipping slow space estimation
SKIP_SIZE_ESTIMATE=false

CHARESC="\033"
CHARNLR="\r"
CHARNLN="\n"



# Track start time
ROOT_PATH="."
SRC_PATH="."
LAST_BACKUP_DATE_FILE="$SRC_PATH/sysbackup/last_successful_backup.txt"
LOG_FILE="$SRC_PATH/sysbackup/backup.log"

# Determine if this is an incremental backup
INCREMENTAL=false
if [ "$1" = "--incremental" ] && [ -f "$LAST_BACKUP_DATE_FILE" ]; then
  INCREMENTAL=true
fi
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

if [ "$SKIP_SIZE_ESTIMATE" != "true" ]; then
  echo "⏳ Calculating estimated backup size..."
  APPDATA_SIZE=0
  DATA_SIZE=0
  [ -d "$SRC_PATH/appdata" ] && APPDATA_SIZE=$(du -sk "$SRC_PATH/appdata" | awk '{print $1}')
  [ -d "$SRC_PATH/public_html/data" ] && DATA_SIZE=$(du -sk "$SRC_PATH/public_html/data" | awk '{print $1}')
  ESTIMATED_TOTAL_KB=$(((APPDATA_SIZE + DATA_SIZE + 10240) * 12 / 10))
  AVAILABLE_SPACE=$(df --output=avail "$BACKUP_PATH" | tail -n 1)
  if [ -z "$AVAILABLE_SPACE" ] || [ "$AVAILABLE_SPACE" -lt "$ESTIMATED_TOTAL_KB" ]; then
    echo "❌ Not enough disk space. Required: $((ESTIMATED_TOTAL_KB / 1024)) MB, Available: $((AVAILABLE_SPACE / 1024)) MB"
    exit 1
  fi
else
  echo "⚠️  Skipping space estimation as configured (SKIP_SIZE_ESTIMATE=true)"
fi

mkdir -p "$BACKUP_TEMP"



# List only tables (exclude views)
#TODO: fix exclude views
TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -Nse "SHOW FULL TABLES IN \`$DB_NAME\` ;")

# Dump only table structure (no data, no views)
mysqldump --no-tablespaces --skip-add-drop-table \
  --no-data \
  -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"  \
  --result-file="$STRUCTURE_FILE" 2>/dev/null

# Dump views, functions, etc
mysqldump --no-tablespaces --skip-add-drop-table \
  --no-data --routines --no-create-info --no-create-db --skip-triggers \
  --skip-add-locks --skip-comments \
  -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  --result-file="$VIEWS_FILE" 2>/dev/null

# Dump data (no structure)
mysqldump --no-tablespaces --skip-add-drop-table \
  --no-create-info \
  --insert-ignore \
  -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  --result-file="$DATA_FILE" 2>/dev/null

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
  echo
  echo "SQL Files Included:"
  echo " - structure.sql   (table definitions, no data)"
  echo " - views.sql       (views, routines, no triggers)"
  echo " - data.sql        (data only, uses INSERT IGNORE)"
  echo
  echo "🗂️ File Extraction and Preparation:"
  echo
  echo "1. Move the backup file to:"
  echo "     sysbackup/restore/"
  echo
  echo "2. Navigate to the restore folder:"
  echo "     cd sysbackup/restore"
  echo
  echo "3. Unzip the backup archive into a folder named after the file:"
  echo "     unzip $ARCHIVE_NAME -d $ARCHIVE_BASENAME"
  echo
  echo "4. (If needed) Set the correct owner and group on the extracted files:"
  echo "     sudo chown -R www-data:webapps $ARCHIVE_BASENAME/"
  echo
  echo "⚠️ Note:"
  echo " - 'www-data' and 'webapps' are just examples."
  echo " - Use the actual user and group that your web server uses."
  echo " - You can check the current owner/group with:"
  echo "     ls -ld ../../appdata ../../public_html/data"
  echo
  echo "🛠️ Restore Database Instructions:"
  echo
  echo "🅰️ Option A (standard MySQL user):"
  echo "1. Create an empty database if needed:"
  echo "     mysql -u USER -p -e 'CREATE DATABASE $DB_NAME;'"
  echo
  echo "2. Import the structure:"
  echo "     mysql -u USER -p $DB_NAME < $ARCHIVE_BASENAME/structure.sql"
  echo
  echo "3. Import the views and routines:"
  echo "     mysql -u USER -p $DB_NAME < $ARCHIVE_BASENAME/views.sql"
  echo
  echo "4. Import the data (skips duplicate entries):"
  echo "     mysql -u USER -p $DB_NAME < $ARCHIVE_BASENAME/data.sql"
  echo
  echo "🅱️ Option B (as root without password prompt):"
  echo "     sudo mysql -e 'CREATE DATABASE $DB_NAME;'"
  echo "     sudo mysql $DB_NAME < $ARCHIVE_BASENAME/structure.sql"
  echo "     sudo mysql $DB_NAME < $ARCHIVE_BASENAME/views.sql"
  echo "     sudo mysql $DB_NAME < $ARCHIVE_BASENAME/data.sql"
  echo
  echo "📁 Restore Files into Project:"
  echo
  echo "5. Copy the files into your live folders:"
  echo
  echo "🅰️ Option A (no sudo, no overwrite):"
  echo "     cp -niv $ARCHIVE_BASENAME/appdata/* ../../appdata/"
  echo "     cp -niv $ARCHIVE_BASENAME/public_html/data/* ../../public_html/data/"
  echo
  echo "⚠️ This option will skip files that already exist and ask before overwriting."
  echo " - Use it only if your user has write permission to the destination folders."
  echo
  echo "🅱️ Option B (with sudo, overwrite, preserve owner/group):"
  echo "     sudo cp -prv $ARCHIVE_BASENAME/appdata/* ../../appdata/"
  echo "     sudo cp -prv $ARCHIVE_BASENAME/public_html/data/* ../../public_html/data/"
  echo
  echo "🅰️ Or use rsync (recommended for servers):"
  echo "     sudo rsync -av --chown=www-data:webapps $ARCHIVE_BASENAME/appdata/ ../../appdata/"
  echo "     sudo rsync -av --chown=www-data:webapps $ARCHIVE_BASENAME/public_html/data/ ../../public_html/data/"
  echo
  echo "🔍 To preview the changes before copying:"
  echo "     rsync -av --dry-run $ARCHIVE_BASENAME/appdata/ ../../appdata/"
  echo
  echo "🧼 Clean up after restoring:"
  echo "     rm -rf $ARCHIVE_BASENAME"
} > "$INFO_FILE"

# Determine time filter if incremental
LAST_BACKUP_DATE_FILE="$BACKUP_PATH/last_successful_backup.txt"
if [ "$INCREMENTAL" = true ] && [ -f "$LAST_BACKUP_DATE_FILE" ]; then
  MODIFIED_SINCE="-newermt \"$(cat $LAST_BACKUP_DATE_FILE)\""
else
  MODIFIED_SINCE=""
fi

# Add appdata if exists
if [ -d "appdata" ]; then
  TOTAL_FILES=$(find appdata -type f | wc -l)
  if [ "$TOTAL_FILES" -eq 0 ]; then
    echo "⚠️  No files found in appdata, skipping."
  else
    printf "➕ Adding appdata to backup...$CHARNLR$CHARNLN"
    FILE_COUNT=0
    BATCH=""
  BATCH=""
  while IFS= read -r file; do
    BATCH+="$file$CHARNLN"
    FILE_COUNT=$((FILE_COUNT + 1))
    if (( FILE_COUNT % PROGRESS_BATCH_SIZE == 0 )); then
      PERCENT=$((FILE_COUNT * 100 / TOTAL_FILES))
      printf "$CHARNLR$CHARESC[K  → %s/%s files from appdata added (%s%%)..." "$FILE_COUNT" "$TOTAL_FILES" "$PERCENT"
    fi
    if (( FILE_COUNT % BATCH_SIZE == 0 )); then
      echo -e "$BATCH" | zip -q "$ARCHIVE_FILE" -@
      BATCH=""
    fi
  done < <(eval find appdata -type f $MODIFIED_SINCE)
  if [ -n "$BATCH" ]; then
    echo -e "$BATCH" | zip -q "$ARCHIVE_FILE" -@
  fi
  printf "$CHARNLR$CHARESC[K  → Total files added from appdata: $FILE_COUNT/$TOTAL_FILES [##################################################]$CHARNLN"
  fi
fi
# Add public_html/data if exists
if [ -d "public_html/data" ]; then
  TOTAL_FILES=$(find public_html/data -type f | wc -l)
  if [ "$TOTAL_FILES" -eq 0 ]; then
    echo "⚠️  No files found in public_html/data, skipping."
  else
    printf "➕ Adding public_html/data to backup...$CHARNLR$CHARNLN"
    FILE_COUNT=0
    BATCH=""
  BATCH=""
  while IFS= read -r file; do
    BATCH+="$file$CHARNLN"
    FILE_COUNT=$((FILE_COUNT + 1))
    if (( FILE_COUNT % PROGRESS_BATCH_SIZE == 0 )); then
      PERCENT=$((FILE_COUNT * 100 / TOTAL_FILES))
      printf "$CHARNLR$CHARESC[K  → %s/%s files from public_html/data added (%s%%)..." "$FILE_COUNT" "$TOTAL_FILES" "$PERCENT"
    fi
    if (( FILE_COUNT % BATCH_SIZE == 0 )); then
      printf "%s" "$BATCH" | zip -q "$ARCHIVE_FILE" -@
      BATCH=""
    fi
  done < <(eval find public_html/data -type f $MODIFIED_SINCE)
  if [ -n "$BATCH" ]; then
    echo -e "$BATCH" | zip -q "$ARCHIVE_FILE" -@
  fi
  printf "$CHARNLR$CHARESC[K  → Total files added from public_html/data: $FILE_COUNT/$TOTAL_FILES [##################################################]$CHARNLN"
  fi
fi
echo "➕ Adding database to backup..."
zip -j -q "$ARCHIVE_FILE" "$STRUCTURE_FILE" "$VIEWS_FILE" "$DATA_FILE"

END_TIME=$(date +%s)
ELAPSED_TIME=$((END_TIME - START_TIME))

if [ -f "$INFO_FILE" ]; then
  echo -e "Execution Time: ${ELAPSED_TIME} seconds" >> "$INFO_FILE"
  echo "➕ Adding info.txt to backup..."
  zip -j -q "$ARCHIVE_FILE" "$INFO_FILE"
fi

TOTAL_BACKED_UP_FILES=$(unzip -l "$ARCHIVE_FILE" | grep -E '^ +[0-9]+' | wc -l)
echo "[$LOG_DATETIME] FILES | 📦 Total files: $TOTAL_BACKED_UP_FILES" >> "$LOG_FILE"
printf "$CHARNLR$CHARESC[K📦 Total files in backup: $TOTAL_BACKED_UP_FILES$CHARNLN"

# Clean up temporary files
rm -f "$BACKUP_TEMP/structure.sql" "$BACKUP_TEMP/views.sql" "$BACKUP_TEMP/data.sql" "$BACKUP_TEMP/info.txt"
rmdir "$BACKUP_TEMP"

END_TIME=$(date +%s)
ELAPSED_TIME=$((END_TIME - START_TIME))
ARCHIVE_SIZE=$(du -h "$ARCHIVE_FILE" | awk '{print $1}')
if unzip -l "$ARCHIVE_FILE" | grep -q "structure.sql"; then
  echo "✅ Backup complete: $ARCHIVE_FILE ($ARCHIVE_SIZE)"
  echo "[$LOG_DATETIME] DONE  | ✅ Completed in ${ELAPSED_TIME}s, file: $ARCHIVE_FILE ($ARCHIVE_SIZE)" >> "$LOG_FILE"
  date -u '+%Y-%m-%d %H:%M:%S UTC' > "$LAST_BACKUP_DATE_FILE"
  echo "⏱️  Execution time: ${ELAPSED_TIME} seconds"
else
  echo "❌ Backup failed: structure.sql not found in archive"
  echo "[$LOG_DATETIME] FAIL  | ❌ structure.sql missing, backup aborted" >> "$LOG_FILE"
  rm -f "$ARCHIVE_FILE"
  exit 2
fi
