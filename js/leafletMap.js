      // Inicialización del mapa con Leaflet en la ubicación de Aguadulce
      var mapa = L.map("map").setView([36.811451, -2.569619], 16);

      // Agregar capa de mapa de OpenStreetMap
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
      }).addTo(mapa);

      // Agregar marcador en la ubicación de la peluquería
      L.marker([36.811451, -2.569619])
        .addTo(mapa)
        .bindPopup("Paseo Marítimo de Aguadulce, Almería")
        .openPopup();