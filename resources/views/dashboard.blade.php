<!DOCTYPE html>
<html>

<head>
    <title>Updates Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: #f7f9fc;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
            color: #2c3e50;
        }

        .package {
            border: 1px solid #ddd;
            padding: 20px 25px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 3px 7px rgb(0 0 0 / 0.1);
            background-color: #fff;
            transition: box-shadow 0.3s ease;
        }

        .package:hover {
            box-shadow: 0 6px 15px rgb(0 0 0 / 0.15);
        }

        .updated {
            border-left: 6px solid #28a745;
            background-color: #e6f4ea;
        }

        .outdated {
            border-left: 6px solid #dc3545;
            background-color: #fbeaea;
        }

        .lts-yes {
            color: #28a745;
            font-weight: 700;
        }

        .lts-no {
            color: #dc3545;
            font-weight: 700;
        }

        button {
            background: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: #0056b3;
        }

        input[type="text"] {
            padding: 10px 15px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        /* Small responsive fix */
        @media(max-width: 600px) {
            body {
                margin: 10px;
            }

            input[type="text"] {
                width: 100%;
                margin-bottom: 10px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <h1>Updates Dashboard</h1>

    <div>
        <h2>Linguagens de Programação</h2>
        <button onclick="checkLanguages()">Verificar Linguagens</button>
        <div id="languages-results"></div>
    </div>

    <div style="margin-top: 40px;">
        <h2>Pacotes Específicos</h2>
        <input type="text" id="package-input" placeholder="Nome do pacote (ex: react)">
        <button onclick="checkPackage()">Verificar Pacote</button>
        <div id="package-results"></div>
    </div>

    <script>
        async function checkLanguages() {
            try {
                const response = await axios.get('/api/updates/languages');
                const languages = response.data.data;

                let html = '';
                for (const [lang, info] of Object.entries(languages)) {
                    if (!info || typeof info !== 'object') {
                        html += `
                            <div class="package outdated">
                                <h3>${lang.toUpperCase()}</h3>
                                <p><strong>Status:</strong> Erro ao buscar dados</p>
                            </div>
                        `;
                        continue;
                    }

                    const version = info.current_version || 'N/A';
                    const date = info.release_date || 'N/A';
                    const source = info.source || 'N/A';
                    const status = info.status || 'unknown';
                    const error = info.error || null;

                    // LTS formatado com emojis e cor
                    let ltsText = 'N/A';
                    let ltsClass = '';
                    if (info.lts !== undefined && info.lts !== null) {
                        if (info.lts === true || (typeof info.lts === 'string' && info.lts.toLowerCase() === 'yes')) {
                            ltsText = '✅ Sim (LTS)';
                            ltsClass = 'lts-yes';
                        } else if (info.lts === false || (typeof info.lts === 'string' && info.lts.toLowerCase() === 'no')) {
                            ltsText = '❌ Não';
                            ltsClass = 'lts-no';
                        } else {
                            ltsText = info.lts;
                        }
                    }

                    const cssClass = status === 'success' ? 'updated' : 'outdated';

                    const sourceHtml = (typeof source === 'string' && (source.startsWith('http') || source.includes('.')))
                        ? `<a href="${source.startsWith('http') ? source : 'https://' + source}" target="_blank" rel="noopener noreferrer">${source}</a>`
                        : source;

                    const warning = (status === 'success' && version === 'N/A') ? '<p style="color: #b85c00"><strong>Atenção:</strong> Não foi possível obter a versão mais recente.</p>' : '';

                    html += `
    <div class="package ${cssClass}">
        <h3>${lang.toUpperCase()}</h3>
        <p><strong>Versão:</strong> ${version}</p>
        <p><strong>Data:</strong> ${date}</p>
        <p><strong>LTS:</strong> <span class="${ltsClass}">${ltsText}</span></p>
        <p><strong>Fonte:</strong> ${sourceHtml}</p>
        <p><strong>Status:</strong> ${status}</p>
        ${warning}
        ${error ? `<p style='color:#b80000'><strong>Erro:</strong> ${error}</p>` : ''}
    </div>
`;
                }

                document.getElementById('languages-results').innerHTML = html;
            } catch (error) {
                console.error('Erro ao buscar linguagens:', error);
                document.getElementById('languages-results').innerHTML = `
                    <div class="package outdated">
                        <h3>Erro</h3>
                        <p>Erro ao buscar dados das linguagens: ${error.message}</p>
                    </div>
                `;
            }
        }

        async function checkPackage() {
            const packageName = document.getElementById('package-input').value.trim();
            if (!packageName) {
                alert('Digite o nome do pacote');
                return;
            }

            try {
                const response = await axios.get(`/api/updates/package/${packageName}`);
                const data = response.data.data;

                let html = '';
                for (const [registry, info] of Object.entries(data)) {
                    if (info && typeof info === 'object') {
                        const name = info.name || packageName;
                        const version = info.version || 'N/A';
                        const description = info.description || 'Sem descrição';
                        const updatedAt = info.updated_at || 'N/A';
                        const source = info.source || registry;

                        html += `
                            <div class="package updated">
                                <h3>${registry.toUpperCase()}</h3>
                                <p><strong>Nome:</strong> ${name}</p>
                                <p><strong>Versão:</strong> ${version}</p>
                                <p><strong>Descrição:</strong> ${description}</p>
                                <p><strong>Atualizado em:</strong> ${updatedAt}</p>
                                <p><strong>Fonte:</strong> ${source}</p>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="package outdated">
                                <h3>${registry.toUpperCase()}</h3>
                                <p><strong>Status:</strong> Pacote não encontrado neste registro</p>
                            </div>
                        `;
                    }
                }

                if (html === '') {
                    html = `
                        <div class="package outdated">
                            <h3>Nenhum resultado</h3>
                            <p>Pacote não encontrado em nenhum registro</p>
                        </div>
                    `;
                }

                document.getElementById('package-results').innerHTML = html;
            } catch (error) {
                console.error('Erro ao buscar pacote:', error);
                document.getElementById('package-results').innerHTML = `
                    <div class="package outdated">
                        <h3>Erro</h3>
                        <p>Erro ao buscar pacote: ${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>

</html>
