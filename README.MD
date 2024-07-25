## Установка

1. Клонируйте репозиторий:
   ```bash
   git clone https://github.com/ox1e/travel-cost-api.git
    ```

2. Перейдите в директорию проекта:
    ```bash   
    cd travel-cost-api
    ```

3. Установите зависимости с помощью Composer:
    ```bash
    composer install
    ```

## Использование
**API**

- **POST /calculate**

  Пример запроса:
    ```json
    {
        "baseCost": 15000,
        "startDate": "2027-05-01",
        "birthDate": "2014-01-01",
        "paymentDate": "2026-11-01"
    }
    ```
    Пример ответа:
    ```json
    {
    "finalCost": 12555
    }
  ```

## Тестирование
Для запуска тестов:
```bash
php bin/phpunit
```