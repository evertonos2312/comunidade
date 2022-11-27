# Setup Docker Para Projetos Laravel (8 ou 9)

### Passo a passo

Ter ambiente Docker configurado com WSL2 e distro linux (Ubuntu)
Em ambiente Windows verificar se está otimizado de acordo com o artigo
```
https://www.createit.com/blog/make-docker-on-windows-fast-again-2022/
```


Clone Repositório
```sh
git clone https://github.com/evertonos2312/comunidade comunidade
```

```sh
cd comunidade/
```


Remova o versionamento
```sh
rm -rf .git/
```


Crie o Arquivo .env
```sh
cd example-project/
cp .env.example .env
```

Atualizar permissões Ubuntu
```
sudo chmod -R 777 * <project location>
```

Atualize as variáveis de ambiente do arquivo .env
```dosini
APP_NAME=EspecializaTi
APP_URL=http://localhost:8989
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=nome_que_desejar_db
DB_USERNAME=nome_usuario
DB_PASSWORD=senha_aqui
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```


Suba os containers do projeto
```sh
docker-compose up -d
```

Se precisar excluir as imagens
```
docker rmi -f $(docker images -a -q)
```


Acessar o container
```sh
docker-compose exec app bash
```


Instalar as dependências do projeto
```dosini
composer install
npm install
```

Atualizar permissões Ubuntu
```
sudo chmod -R 777 * <project location>
```


Gerar a key do projeto Laravel
```sh
php artisan key:generate
```

Manter NPM em execução em terminal
```sh
npm run dev
```

Acessar o projeto
[http://localhost:8989](http://localhost:8989)
