(() => {
    const elementoMapa = document.getElementById("map");
    const elementoStatus = document.getElementById("mapa-status");

    if (!elementoMapa) return;

    function mostrarStatus(mensagem) {
        if (elementoStatus) {
            elementoStatus.textContent = mensagem;
        }
    }

    const chaveApi = window.GOOGLE_MAPS_API_KEY;

    if (!chaveApi || chaveApi === "COLOQUE_SUA_CHAVE_AQUI") {
        mostrarStatus("Configure sua chave da Google Maps JavaScript API no arquivo frontend/js/maps-config.js.");
        return;
    }

    window.initMap = function () {
        const enderecoETEC = "ETEC Profª Ilza Nascimento Pintus, Avenida Salmão, 570, Parque Residencial Aquarius, São José dos Campos, SP, Brasil";

        const mapa = new google.maps.Map(elementoMapa, {
            center: { lat: -23.206, lng: -45.895 },
            zoom: 16,
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true
        });

        const geocoder = new google.maps.Geocoder();

        geocoder.geocode(
            {
                address: enderecoETEC,
                componentRestrictions: { country: "BR" }
            },
            (resultados, status) => {
                if (status === "OK" && resultados[0]) {
                    const localizacao = resultados[0].geometry.location;

                    mapa.setCenter(localizacao);

                    new google.maps.Marker({
                        map: mapa,
                        position: localizacao,
                        title: "ETEC Profª Ilza Nascimento Pintus"
                    });

                    mostrarStatus("");
                } else {
                    mostrarStatus("O mapa foi carregado, mas não foi possível localizar o endereço automaticamente. Use o link abaixo para abrir a localização.");
                }
            }
        );
    };

    const scriptMaps = document.createElement("script");
    scriptMaps.src = "https://maps.googleapis.com/maps/api/js?key="
        + encodeURIComponent(chaveApi)
        + "&callback=initMap&loading=async&language=pt-BR&region=BR";
    scriptMaps.async = true;
    scriptMaps.onerror = () => {
        mostrarStatus("Não foi possível carregar o Google Maps. Confira a chave de API e as permissões configuradas no Google Cloud.");
    };

    document.head.appendChild(scriptMaps);
})();
