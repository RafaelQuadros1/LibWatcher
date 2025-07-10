## Rotas disponíveis

Prefixo base: /api/updates

| Método | Rota                     | Descrição                                   |
|--------|--------------------------|---------------------------------------------|
| GET    | /languages               | Retorna dados de updates de linguagens (PHP, JS, Java) |
| GET    | /libraries               | Busca atualizações para bibliotecas específicas (query param libraries) |
| GET    | /package/{package}       | Busca info de um pacote específico em npm, Packagist e PyPI |
| GET    | /github/{owner}/{repo}   | Busca a última release de um repositório GitHub |

---
