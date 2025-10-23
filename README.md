# 🚀 Laravel Inventory & Sales API

API REST desenvolvida com **Laravel 12+** e **PHP 8.4**, seguindo as melhores práticas de escalabilidade, performance e concorrência.

---

## 🧱 Tecnologias Utilizadas

- **PHP 8.4** (via Docker)
- **Laravel 12+**
- **MySQL 8.0**
- **Redis** (cache e filas)
- **PHPStan + Larastan** (lint estático)
- **PHPUnit** (testes unitários)
- **Supervisor / Queue Worker**
- **Scheduler (tarefas agendadas)**

---

## ⚙️ Instalação e Execução
Foi criado um arquivo `Makefile` para facilitar a execução de comandos na fase de desenvolvimento.
Abra o arquivo `Makefile` na raiz do projeto e veja os camandos disponíveis
### 1️⃣ Clonar o projeto

```bash
git clone https://github.com/ludmilla-carvalho/laravel-inventory-api

cd laravel-inventory-api
```

### 2️⃣ Subir containers

```bash
make up
```

### 3️⃣ Instalar dependências

```bash
make install
```

### 4️⃣ Configurar o .env
Copie o arquivo `.env.example` e ajuste se necessário:
```bash
cp .env.example .env
make bash
php artisan key:generate
exit
```

### 5️⃣ Executar migrações e seeders
Seeders criados:
- RecentInventorySeeder - com o campo last_updated entre 1 e 30 dias
- OldInventorySeeder - com o campo last_updated de 120 dias
```bash
make migrate
```

### 6️⃣ Rodar servidor
O sistema estará acessível em http://localhost

O PHPMyAdmin está acessível em http://localhost:8080  

## 🖌 Testes e Qualidade

### Rodar testes unitários

```bash
make test
```

### Formatação de Código - Laravel Pint
O projeto utiliza **Laravel Pint** para padronizar o código PHP.
```bash
make format
```

### Rodar análise estática (PHPStan)

```bash
make lint
```

### Pré-commit Hook
Um hook Git foi configurado para rodar Pint e o PHPStan automaticamente antes de cada commit. Este hoock está localizado na pasta `extras` e deve ser copiado para a pasta `.git/hooks`
```bash
cp .extras/pre-commit .git/hooks/pre-commit
```
- Certifique-se que `.git/hooks/pre-commit` existe e é executável:
```bash
chmod +x .git/hooks/pre-commit
```
 - Ao tentar commitar código PHP:
   - Se houver problemas de formatação, o commit será bloqueado e será necessário corrigir.
   - Se tudo estiver correto, o commit será realizado normalmente.


## 🧵 Fila e Concorrência
O processamento de vendas é assíncrono via Redis Queue através do `Job` `ProcessSaleJob`.
- Job executa com transações e locks (`lockForUpdate()`) para evitar concorrência simultânea.
- Cache do estoque é invalidado (`Cache::forget`) após atualização.

## ⏰ Tarefas Agendadas (Scheduler)
O container scheduler executa o comando `php artisan schedule:run` a cada minuto.

A limpeza automática de registros de estoque antigos (não atualizados há 90 dias) é agendada diariamente no `App\Console\Kernel`.

Também é possível realizar esta limpeza pelo artisan
Logs:
```bash
 php artisan inventory:clean
```
**Na seed há registros com mais de 90 dias para fins de teste**


## ⚡ Estratégias de Otimização Implementadas
- **Cache de consultas de estoque** com Redis (`GET /api/inventory`)
- **Transações com** `lockForUpdate(`) para garantir integridade de estoque
- **Filas assíncronas** para atualização de estoque e processamento de vendas
- **Jobs e Events** desacoplados
- **Tarefas agendadas** com `schedule:run` em container dedicado
- **Lint** + **Testes integrados** (PHPStan + PHPUnit)

## 📂 Estrutura dos Endpoints
| Método   | Endpoint             | Descrição                          |
| :------- | :------------------- | :--------------------------------- |
| **POST** | `/api/inventory`     | Registrar entrada de produtos      |
| **GET**  | `/api/inventory`     | Consultar estoque atual (cacheado) |
| **POST** | `/api/sales`         | Registrar venda (assíncrona)       |
| **GET**  | `/api/sales/{id}`    | Detalhar venda                     |
| **GET**  | `/api/reports/sales` | Relatório de vendas filtrado       |

## 🧑‍💻 Desenvolvimento
Acesse o container para rodar comandos artisan:
```bash
make bash
php artisan route:list
php artisan tinker
exit
```

## 📦 Deploy e Produção
Para produção:

- Configure `.env` com `APP_ENV=production` e `APP_DEBUG=false`.
- Ajuste volumes e persistência no `docker-compose.prod.yml`.
- Configure Redis externo e banco de dados gerenciado, se aplicável.