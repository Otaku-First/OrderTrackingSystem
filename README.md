
# Laravel Project: Order Tracking System

## Установка

1. **Клонування репозиторію**
   ```bash
   git clone https://github.com/your-repo-url.git
   cd your-repo-folder
   ```

2. **Встановлення залежностей**
   ```bash
   composer install
   npm install && npm run dev
   ```

3. **Створення `.env` файлу**
   Скопіюйте файл `.env.example` і назвіть його `.env`:
   ```bash
   cp .env.example .env
   ```

   Заповніть конфігурації у файлі `.env`:
    - **APP_URL**: Адреса вашого проєкту (наприклад, `http://localhost`).
    - **DB_CONNECTION**: `mysql` або інший драйвер бази даних.
    - **DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD**: Налаштування доступу до вашої бази даних.
    - **JWT_SECRET**: Згенеруйте секретний ключ для JWT.

4. **Генерація ключів**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Міграція бази даних**
   Виконайте міграції для створення таблиць:
   ```bash
   php artisan migrate
   ```

6. **Запуск сервера**
   Запустіть локальний сервер Laravel:
   ```bash
   php artisan serve
   ``` 
   або
   ```bash
   docker-compose up
   ```

   Проєкт буде доступний за адресою [http://localhost:8000](http://localhost:8000).

7. **Запуск документації Swagger**
   Згенеруйте документацію API:
   ```bash
   php artisan l5-swagger:generate
   ```

   Swagger UI доступний за адресою: `http://localhost:8000/api/documentation`, або scribe згенерована документація `http://localhost:8000/docs`.


