
Globalize.addCultureInfo("es", {
	numberFormat: {
		",": ",",
		".": ".",
		"NaN": "NeuN",
		negativeInfinity: "-Infinito",
		positiveInfinity: "Infinito",
		percent: {
			",": ",",
			".": "."
		},
		currency: {
			pattern: ["-n $","n $"],
			",": ",",
			".": ".",
			symbol: "€"
		}
	},

    "messages": {
		
        "Yes": "Sí",
        "No": "No",
        "Accept": "Aceptar",
        "Cancel": "Cancelar",
        "Clear": "Limpiar",
        "Done": "Listo",
        "Loading": "Cargando...",
        "Select": "Seleccionar...",
		Advanced: "Avanzado",

        "Search": "Buscar...",
        "Back": "Regresar",
        "OK": "OK",
		"New":"Nuevo",
		"%lbl.time.justnow" : "Ahora",
		"%lbl.datetime.minute" : "minuto",
		"%lbl.datetime.minutes" : "minutos",
		"%lbl.datetime.hour" : "hora",
		"%lbl.datetime.hours" : "horas",
		"%lbl.datetime.day" : "día",
		"%lbl.datetime.days" : "días",
		"%lbl.datetime.yesterday" : "Ayer",
		"mw.datetime.relative.past" : ["Hace ",{argindex:0}," ",{argindex:1}],
		

	},
	calendar: {
		patterns: {
			x:"d-M-yyyy H:mm",	
		}
	},
	calendars: {
		standard: {
			patterns: {
				x:"d-M-yyyy H:mm",	
			}
		}
	}

});

