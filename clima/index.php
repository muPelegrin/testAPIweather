<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsão do Tempo Completa</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

    <div class="app-shell">
        <aside class="location-panel" aria-label="Filtros de local">
            <div class="panel-header">
                <span class="filter-badge">Precisão</span>
                <h2>Filtros de local</h2>
            </div>

            <div class="selection-summary" id="selection-summary">Nenhum local selecionado</div>
            <div class="location-status" id="location-status">Carregando opções...</div>

            <div class="filters-scroll" aria-label="Opções de filtragem">
            <div class="picker-section">
                <div class="picker-heading">
                    <span>Continente</span>
                    <strong id="continent-count">0</strong>
                </div>
                <div class="option-list continent-list" id="continent-list"></div>
            </div>

            <div class="picker-section">
                <div class="picker-heading">
                    <span>País</span>
                    <strong id="country-count">0</strong>
                </div>
                <input type="search" class="list-search" id="country-list-search" placeholder="Filtrar países" autocomplete="off">
                <div class="option-list" id="country-list"></div>
            </div>

            <div class="picker-section">
                <div class="picker-heading">
                    <span>Estado ou província</span>
                    <strong id="state-count">0</strong>
                </div>
                <input type="search" class="list-search" id="state-list-search" placeholder="Filtrar estados" autocomplete="off">
                <div class="option-list" id="state-list"></div>
            </div>

            <div class="picker-section">
                <div class="picker-heading">
                    <span>Cidade ou município</span>
                    <strong id="city-count">0</strong>
                </div>
                <input type="search" class="list-search" id="city-list-search" placeholder="Filtrar cidades" autocomplete="off">
                <div class="option-list" id="city-list"></div>
            </div>

            </div>

            <button type="button" class="panel-search-btn" id="panel-search-btn" disabled>Buscar local selecionado</button>
        </aside>

        <div class="weather-card">
            <h1>Previsão do Tempo</h1>
            
            <div class="search-box">
                <input type="text" id="city-input" placeholder="Cidade ou município..." autocomplete="off">
                <button id="search-btn">🔍</button>
            </div>

            <div id="loading">Buscando dados na atmosfera...</div>
            <div id="error-message"></div>

            <div id="weather-result">
                <div class="weather-main">
                    <h2 id="city-name">--</h2>
                    <div class="coord-text" id="coordinates">Lat: -- | Lon: --</div>
                    
                    <div class="temp">
                        <img id="weather-icon" src="" alt="Ícone do clima" style="display:none;">
                        <span id="temperature">--°C</span>
                    </div>
                    
                    <div class="desc" id="description">--</div>
                    <div class="feels-like">Sensação térmica de <strong id="feels-like">--°C</strong></div>
                </div>
                
                <div class="weather-grid">
                    <div class="grid-item">
                        <span>Min / Máx</span>
                        <strong><span id="temp-min">--</span>°C / <span id="temp-max">--</span>°C</strong>
                    </div>
                    <div class="grid-item">
                        <span>Umidade</span>
                        <strong id="humidity">--%</strong>
                    </div>
                    <div class="grid-item">
                        <span>Vento</span>
                        <strong id="wind-speed">-- km/h</strong>
                    </div>
                    <div class="grid-item">
                        <span>Pressão</span>
                        <strong id="pressure">-- hPa</strong>
                    </div>
                    <div class="grid-item">
                        <span>Visibilidade</span>
                        <strong id="visibility">-- km</strong>
                    </div>
                    <div class="grid-item">
                        <span>Sol (Nascer / Pôr)</span>
                        <strong><span id="sunrise">--:--</span> / <span id="sunset">--:--</span></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchBtn = document.getElementById('search-btn');
            const panelSearchBtn = document.getElementById('panel-search-btn');
            const cityInput = document.getElementById('city-input');
            const loadingDiv = document.getElementById('loading');
            const errorDiv = document.getElementById('error-message');
            const resultDiv = document.getElementById('weather-result');
            const weatherCard = document.querySelector('.weather-card');
            const locationStatus = document.getElementById('location-status');
            const selectionSummary = document.getElementById('selection-summary');
            const continentList = document.getElementById('continent-list');
            const countryList = document.getElementById('country-list');
            const stateList = document.getElementById('state-list');
            const cityList = document.getElementById('city-list');
            const countrySearch = document.getElementById('country-list-search');
            const stateSearch = document.getElementById('state-list-search');
            const citySearch = document.getElementById('city-list-search');
            const countEls = {
                continents: document.getElementById('continent-count'),
                countries: document.getElementById('country-count'),
                states: document.getElementById('state-count'),
                cities: document.getElementById('city-count')
            };
            const bodyThemeClasses = [
                'bg-clear',
                'bg-clouds',
                'bg-rain',
                'bg-drizzle',
                'bg-thunderstorm',
                'bg-snow',
                'bg-mist',
                'bg-hot',
                'bg-cold',
                'bg-spring',
                'bg-autumn',
                'bg-night'
            ];
            const cardThemeClasses = [
                'clima-dia',
                'clima-tarde',
                'clima-tempestade',
                'clima-noite'
            ];
            const continentOrder = [
                'África',
                'América do Norte',
                'América Central',
                'América do Sul',
                'Ásia',
                'Europa',
                'Oceania',
                'Antártida'
            ];
            const locality = {
                countries: [],
                cities: [],
                citiesCache: new Map(),
                selected: {
                    continent: '',
                    country: null,
                    state: null,
                    city: ''
                },
                loadingCities: false
            };

            const els = {
                cityName: document.getElementById('city-name'),
                coordinates: document.getElementById('coordinates'),
                temperature: document.getElementById('temperature'),
                icon: document.getElementById('weather-icon'),
                description: document.getElementById('description'),
                feelsLike: document.getElementById('feels-like'),
                tempMin: document.getElementById('temp-min'),
                tempMax: document.getElementById('temp-max'),
                humidity: document.getElementById('humidity'),
                windSpeed: document.getElementById('wind-speed'),
                pressure: document.getElementById('pressure'),
                visibility: document.getElementById('visibility'),
                sunrise: document.getElementById('sunrise'),
                sunset: document.getElementById('sunset')
            };

            searchBtn.addEventListener('click', fetchWeather);
            panelSearchBtn.addEventListener('click', fetchWeather);
            cityInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') fetchWeather();
            });
            cityInput.addEventListener('input', () => {
                if (cityInput.value.trim() !== locality.selected.city) {
                    locality.selected.city = '';
                    renderLocationUi();
                }
            });
            [countrySearch, stateSearch, citySearch].forEach((input) => {
                input.addEventListener('input', renderLocationUi);
            });

            function formatTime(unixTimestamp) {
                const date = new Date(unixTimestamp * 1000);
                return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            }

            function getSeasonTheme(month) {
                if (month >= 8 && month <= 10) return 'bg-spring';
                if (month >= 2 && month <= 4) return 'bg-autumn';
                return null;
            }

            function getWeatherTheme(data) {
                const weatherId = data.weather[0].id;
                const weatherMain = data.weather[0].main.toLowerCase();
                const temp = data.main.temp;
                const now = data.dt;
                const sunrise = data.sys.sunrise;
                const sunset = data.sys.sunset;
                const localMonth = new Date((now + data.timezone) * 1000).getUTCMonth();
                const isNight = now < sunrise || now > sunset;

                if (isNight) return { body: 'bg-night', card: 'clima-noite' };
                if (weatherId >= 200 && weatherId < 300) return { body: 'bg-thunderstorm', card: 'clima-tempestade' };
                if (weatherId >= 300 && weatherId < 400) return { body: 'bg-drizzle', card: 'clima-tempestade' };
                if (weatherId >= 500 && weatherId < 600) return { body: 'bg-rain', card: 'clima-tempestade' };
                if (weatherId >= 600 && weatherId < 700) return { body: 'bg-snow', card: 'clima-noite' };
                if (weatherId >= 700 && weatherId < 800) return { body: 'bg-mist', card: 'clima-tempestade' };
                if (temp >= 32) return { body: 'bg-hot', card: 'clima-tarde' };
                if (temp <= 10) return { body: 'bg-cold', card: 'clima-noite' };
                if (weatherMain === 'clouds') return { body: 'bg-clouds', card: 'clima-dia' };

                return { body: getSeasonTheme(localMonth) || 'bg-clear', card: 'clima-dia' };
            }

            function applyWeatherTheme(data) {
                const theme = getWeatherTheme(data);

                document.body.classList.remove(...bodyThemeClasses);
                weatherCard.classList.remove(...cardThemeClasses);

                document.body.classList.add(theme.body);
                weatherCard.classList.add(theme.card);
            }

            function normalizeText(text) {
                return String(text || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase();
            }

            function setStatus(message, type = 'muted') {
                locationStatus.textContent = message;
                locationStatus.className = `location-status ${type}`;
            }

            function setListMessage(container, message) {
                container.textContent = '';
                const empty = document.createElement('div');
                empty.className = 'empty-state';
                empty.textContent = message;
                container.appendChild(empty);
            }

            function renderOptionList(container, items, config) {
                container.textContent = '';

                if (items.length === 0) {
                    setListMessage(container, config.emptyMessage);
                    return;
                }

                const fragment = document.createDocumentFragment();
                items.forEach((item) => {
                    const key = config.key(item);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'option-btn';
                    button.dataset.key = key;
                    if (key === config.selectedKey) button.classList.add('is-active');

                    const label = document.createElement('span');
                    label.textContent = config.label(item);
                    button.appendChild(label);

                    const metaText = config.meta ? config.meta(item) : '';
                    if (metaText) {
                        const meta = document.createElement('small');
                        meta.textContent = metaText;
                        button.appendChild(meta);
                    }

                    button.addEventListener('click', () => config.onSelect(item));
                    fragment.appendChild(button);
                });

                container.appendChild(fragment);
            }

            function filterItems(items, query, fields) {
                const normalizedQuery = normalizeText(query);
                if (!normalizedQuery) return items;

                return items.filter((item) => {
                    return fields.some((field) => normalizeText(field(item)).includes(normalizedQuery));
                });
            }

            function getContinents() {
                const available = new Set(locality.countries.map((country) => country.continente).filter(Boolean));
                return continentOrder.filter((continent) => available.has(continent));
            }

            function getVisibleCountries() {
                const baseCountries = locality.selected.continent
                    ? locality.countries.filter((country) => country.continente === locality.selected.continent)
                    : locality.countries;

                return filterItems(baseCountries, countrySearch.value, [
                    (country) => country.nome,
                    (country) => country.nome_api,
                    (country) => country.codigo
                ]);
            }

            function getVisibleStates() {
                if (!locality.selected.country) return [];
                const states = locality.selected.country.estados || [];

                return filterItems(states, stateSearch.value, [
                    (state) => state.nome,
                    (state) => state.codigo
                ]);
            }

            function getVisibleCities() {
                return filterItems(locality.cities, citySearch.value, [(city) => city]);
            }

            function resetAfter(level) {
                if (level === 'continent') {
                    locality.selected.country = null;
                    locality.selected.state = null;
                    locality.selected.city = '';
                    locality.cities = [];
                    countrySearch.value = '';
                    stateSearch.value = '';
                    citySearch.value = '';
                    cityInput.value = '';
                }

                if (level === 'country') {
                    locality.selected.state = null;
                    locality.selected.city = '';
                    locality.cities = [];
                    stateSearch.value = '';
                    citySearch.value = '';
                    cityInput.value = '';
                }

                if (level === 'state') {
                    locality.selected.city = '';
                    locality.cities = [];
                    citySearch.value = '';
                    cityInput.value = '';
                }
            }

            function selectContinent(continent) {
                locality.selected.continent = continent;
                resetAfter('continent');
                setStatus('Países atualizados pelo continente.', 'ok');
                renderLocationUi();
            }

            function selectCountry(country) {
                locality.selected.country = country;
                locality.selected.continent = country.continente || locality.selected.continent;
                resetAfter('country');

                if ((country.estados || []).length === 0) {
                    loadCitiesForSelection();
                } else {
                    setStatus('Estados e províncias atualizados pelo país.', 'ok');
                }

                renderLocationUi();
            }

            function selectState(state) {
                locality.selected.state = state;
                resetAfter('state');
                renderLocationUi();
                loadCitiesForSelection();
            }

            function selectCity(city) {
                locality.selected.city = city;
                cityInput.value = city;
                renderLocationUi();
                fetchWeather();
            }

            function updateSelectionSummary() {
                const parts = [
                    locality.selected.continent,
                    locality.selected.country?.nome,
                    locality.selected.state?.nome,
                    locality.selected.city || cityInput.value.trim()
                ].filter(Boolean);

                selectionSummary.textContent = parts.length ? parts.join(' > ') : 'Nenhum local selecionado';
                panelSearchBtn.disabled = !buildWeatherParams();
            }

            function renderLocationUi() {
                const continents = getContinents();
                const countries = getVisibleCountries();
                const states = getVisibleStates();
                const cities = getVisibleCities();
                const countryHasStates = (locality.selected.country?.estados || []).length > 0;

                countEls.continents.textContent = continents.length;
                countEls.countries.textContent = countries.length;
                countEls.states.textContent = states.length;
                countEls.cities.textContent = cities.length;
                stateSearch.disabled = !locality.selected.country || !countryHasStates;
                citySearch.disabled = !locality.selected.country || (countryHasStates && !locality.selected.state);

                renderOptionList(continentList, continents, {
                    key: (continent) => continent,
                    label: (continent) => continent,
                    selectedKey: locality.selected.continent,
                    emptyMessage: 'Nenhum continente carregado',
                    onSelect: selectContinent
                });

                renderOptionList(countryList, countries, {
                    key: (country) => country.codigo,
                    label: (country) => country.nome,
                    meta: (country) => `${country.codigo} · ${country.continente}`,
                    selectedKey: locality.selected.country?.codigo || '',
                    emptyMessage: 'Nenhum país correspondente',
                    onSelect: selectCountry
                });

                if (!locality.selected.country) {
                    setListMessage(stateList, 'Selecione um país');
                } else if (!countryHasStates) {
                    setListMessage(stateList, 'Sem estados cadastrados');
                } else {
                    renderOptionList(stateList, states, {
                        key: (state) => state.nome,
                        label: (state) => state.nome,
                        meta: (state) => state.codigo,
                        selectedKey: locality.selected.state?.nome || '',
                        emptyMessage: 'Nenhum estado correspondente',
                        onSelect: selectState
                    });
                }

                if (locality.loadingCities) {
                    setListMessage(cityList, 'Carregando cidades...');
                } else if (!locality.selected.country) {
                    setListMessage(cityList, 'Selecione um país');
                } else if (countryHasStates && !locality.selected.state) {
                    setListMessage(cityList, 'Selecione um estado');
                } else {
                    renderOptionList(cityList, cities, {
                        key: (city) => city,
                        label: (city) => city,
                        selectedKey: locality.selected.city,
                        emptyMessage: 'Nenhuma cidade correspondente',
                        onSelect: selectCity
                    });
                }

                updateSelectionSummary();
            }

            async function loadLocationData() {
                setStatus('Carregando opções...', 'muted');
                try {
                    const response = await fetch('localidades-api.php?tipo=paises');
                    const data = await response.json();

                    if (!response.ok || data.erro) {
                        throw new Error(data.erro || 'Erro ao carregar localidades.');
                    }

                    locality.countries = data.paises || [];
                    setStatus('Opções carregadas.', 'ok');
                    renderLocationUi();
                } catch (error) {
                    setStatus(error.message, 'error');
                    renderLocationUi();
                }
            }

            async function loadCitiesForSelection() {
                if (!locality.selected.country) return;

                const countryHasStates = (locality.selected.country.estados || []).length > 0;
                if (countryHasStates && !locality.selected.state) return;

                const cacheKey = [
                    locality.selected.country.nome_api,
                    locality.selected.state?.nome || ''
                ].join('|');

                if (locality.citiesCache.has(cacheKey)) {
                    locality.cities = locality.citiesCache.get(cacheKey);
                    setStatus('Cidades atualizadas.', 'ok');
                    renderLocationUi();
                    return;
                }

                locality.loadingCities = true;
                setStatus('Carregando cidades...', 'muted');
                renderLocationUi();

                const params = new URLSearchParams({
                    tipo: 'cidades',
                    pais_api: locality.selected.country.nome_api
                });

                if (locality.selected.state?.nome) {
                    params.set('estado', locality.selected.state.nome);
                }

                try {
                    const response = await fetch(`localidades-api.php?${params.toString()}`);
                    const data = await response.json();

                    if (!response.ok || data.erro) {
                        throw new Error(data.erro || 'Erro ao carregar cidades.');
                    }

                    locality.cities = data.cidades || [];
                    locality.citiesCache.set(cacheKey, locality.cities);
                    setStatus('Cidades carregadas.', 'ok');
                } catch (error) {
                    locality.cities = [];
                    setStatus(error.message, 'error');
                } finally {
                    locality.loadingCities = false;
                    renderLocationUi();
                }
            }

            function buildWeatherParams() {
                const city = locality.selected.city || cityInput.value.trim();
                if (!city) return null;

                const params = new URLSearchParams({ cidade: city });
                const countryCode = locality.selected.country?.codigo || '';
                const state = locality.selected.state?.nome || '';
                const continent = locality.selected.continent || '';

                if (countryCode) params.set('pais', countryCode);
                if (state) params.set('estado', state);
                if (continent) params.set('continente', continent);

                return params;
            }

            async function fetchWeather() {
                const params = buildWeatherParams();
                if (!params) {
                    showError("Escolha uma cidade no painel ou digite o nome da cidade.");
                    return;
                }

                hideAll();
                loadingDiv.style.display = 'block';

                try {
                    const response = await fetch(`clima-api.php?${params.toString()}`);
                    const data = await response.json();

                    if (!response.ok || data.erro) {
                        throw new Error(data.erro || 'Erro ao buscar dados da cidade.');
                    }

                    els.cityName.textContent = data.localizacao?.nome_completo || `${data.name}, ${data.sys.country}`;
                    els.coordinates.textContent = `Lat: ${data.coord.lat.toFixed(2)} | Lon: ${data.coord.lon.toFixed(2)}`;
                    els.temperature.textContent = `${Math.round(data.main.temp)}°C`;
                    els.description.textContent = data.weather[0].description;
                    els.feelsLike.textContent = Math.round(data.main.feels_like);
                    els.tempMin.textContent = Math.round(data.main.temp_min);
                    els.tempMax.textContent = Math.round(data.main.temp_max);
                    els.icon.src = `https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png`;
                    els.icon.style.display = 'block';
                    els.humidity.textContent = `${data.main.humidity}%`;
                    els.pressure.textContent = `${data.main.pressure} hPa`;
                    els.windSpeed.textContent = `${Math.round(data.wind.speed * 3.6)} km/h`;
                    els.visibility.textContent = `${(data.visibility / 1000).toFixed(1)} km`;
                    els.sunrise.textContent = formatTime(data.sys.sunrise);
                    els.sunset.textContent = formatTime(data.sys.sunset);

                    applyWeatherTheme(data);

                    loadingDiv.style.display = 'none';
                    resultDiv.style.display = 'block';

                } catch (error) {
                    loadingDiv.style.display = 'none';
                    showError(error.message);
                }
            }

            function showError(message) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                resultDiv.style.display = 'none';
            }

            function hideAll() {
                loadingDiv.style.display = 'none';
                errorDiv.style.display = 'none';
                resultDiv.style.display = 'none';
            }

            renderLocationUi();
            loadLocationData();
        });
    </script>
</body>
</html>
