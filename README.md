# Свадебная медиагалерея

Laravel/Vue/Inertia-приложение для публичной загрузки свадебных фото и видео без авторизации гостей. Оригиналы файлов загружаются сервером на Яндекс Диск владельца, OAuth-токен не попадает во frontend. Метаданные хранятся в MariaDB, локально сохраняются только превью изображений.

## Установка

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate
```

Для разработки запустите frontend и backend:

```bash
npm run dev
php artisan serve
```

## Переменные окружения

Добавьте или проверьте в `.env`:

```env
YANDEX_DISK_TOKEN=
YANDEX_DISK_BASE_PATH=/WeddingPhotos
YANDEX_DISK_ORIGINALS_PATH=/WeddingPhotos/originals
YANDEX_DISK_THUMBS_PATH=/WeddingPhotos/thumbs
WEDDING_UPLOADS_ENABLED=true
WEDDING_MAX_FILE_SIZE_MB=300
WEDDING_MAX_FILES_PER_REQUEST=10
WEDDING_UPLOAD_THROTTLE=20,1
```

* `WEDDING_UPLOADS_ENABLED=false` выключает публичную загрузку.
* `WEDDING_MAX_FILE_SIZE_MB` — максимальный размер одного файла.
* `WEDDING_MAX_FILES_PER_REQUEST` — сколько файлов можно отправить за один запрос.
* `WEDDING_UPLOAD_THROTTLE=20,1` — не чаще 20 запросов в минуту с одного IP.

## OAuth-токен Яндекс Диска

1. Создайте приложение в Яндекс OAuth: https://oauth.yandex.ru/client/new.
2. Выдайте приложению права Яндекс Диска на чтение и запись файлов.
3. Получите OAuth-токен для аккаунта владельца диска.
4. Сохраните токен только в `.env` в `YANDEX_DISK_TOKEN`.

Не добавляйте токен в JavaScript и не отдавайте гостям ссылку на папку с правами редактирования.

## Функциональность

### Гость

* Открывает `/`.
* Указывает имя.
* Выбирает один или несколько файлов.
* Видит прогресс, успешную загрузку или понятную ошибку.
* Видит общую галерею без кнопок удаления.

Поддерживаемые типы: `jpg`, `jpeg`, `png`, `webp`, `heic`, `heif`, `mp4`, `mov`, `webm`.

### Администратор

После входа через стандартную авторизацию Laravel/Breeze доступна страница `/admin/wedding/media`.

Администратор может:

* просматривать все файлы;
* скачивать файл через безопасный backend-route;
* скрывать файл из публичной галереи;
* возвращать скрытый файл;
* удалять файл: запись помечается `deleted`, а оригинал удаляется с Яндекс Диска.

## Превью

Для изображений `jpg`, `jpeg`, `png`, `webp` при наличии PHP GD создаётся локальное превью шириной до 600px в `storage/app/public/wedding/thumbs`. Для HEIC/HEIF и видео показывается заглушка, так как стандартный GD обычно не умеет декодировать HEIC/HEIF.

## Проверка

```bash
composer install
npm install
php artisan migrate
npm run dev
php artisan serve
```

Для production-сборки frontend:

```bash
npm run build
```
