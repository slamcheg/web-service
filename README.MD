# Web Service

Сервис использует:
- "symfony/dependency-injection": "^4.0",
- "symfony/http-foundation": "^4.0",
- "symfony/routing": "^4.0",
- "symfony/config": "^4.0",
- "monolog/monolog": "^1.23",
- "guzzlehttp/guzzle": "~6.0"

Сервис реализовывает логирование запросов и ответов которые проходят через него.
Запросы и Ответы реализованы с помощью `Symfony Http Foundation Component` при отправлении запраса
он отправляеться на обработу в класс `Proxy\Service\Core\Kernel.php` где происходит его логирование
и роутиг который реализован с помощью `Symfont Roution Component`. После того как запрос был обработан
и было найдено совпадение с сохранёнными роутами, вызываеться класс контроллер с помощью `Symfony DI Component`
Далее пользователю отправляеться ответ который так же логируется.

На данный момен сервис реализовывает контроллер : `Proxy\Service\Controllers\DefaultController.php`
который обращаеться к `GitHub Search Api` c помощью `Guzzle\Client`.

На реализацию было потрачено 4-5 часов.

Для тестирования сервиса необходимо:

- git clone https://github.com/slamcheg/web-service.git
-  Перейти в папку с проектом
- `composer install` 
-  Открыть консоль и прописать
 - `php -S localhost:3000 -t web`
- Для проверки логирования запроса:

- `curl -d '{"q":"your string"}' -H "Content-Type: application/json" -X POST http://localhost:3000/index`
 - Логи создаються в корне проекта в папке `logs`
