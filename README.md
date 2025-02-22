# Imageable

Imageable is an Eloquent model extension that allows you to easily manage images.

## Installation

```bash
composer require elysiumrealms/imageable
```

## Migrations

```bash
php artisan migrate
```

## Usage

Use the `Imageable` trait to your model.

```php
use Elysiumrealms\Imageable;

class User extends Model
implements Imageable\Contracts\Imageable
{
    use Imageable\Traits\ImageableTrait;

    ...
}
```

-   Upload images through the `POST /api/v1/imageable/{collection}` route.

    ```bash
    curl -X POST http://localhost:8000/api/v1/imageable/default \
        -H "Authorization: Bearer {token}" \
        -H "Content-Type: multipart/form-data" \
        -F "images[]=@/path/to/your/image.jpg" \
        -F "images[]=@/path/to/your/image.jpg" \
        -F "images[]=@/path/to/your/image.jpg"
    ```

-   Delete images through the `DELETE /api/v1/imageable` route.

    ```bash
    curl -X DELETE http://localhost:8000/api/v1/imageable/ \
        -H "Authorization: Bearer {token}"
    -H "Content-Type: application/json"
    -d '{"images": [1, 2, 3]}'
    ```

-   Get images paginated through the `GET /api/v1/imageable/{collection}` route.

    ```bash
    curl -X GET http://localhost:8000/api/v1/imageable/default \
        -H "Authorization: Bearer {token}"
        -H "Content-Type: application/json"
        -d '{"page": 1, "per_page": 10}'
    ```

## Prune Resized Images

Run the following command to prune the resized images.

```bash
php artisan imageable:prune
```

## Schedule Prune Resized Images

Add the following to your `schedule` method.

```php
function schedule(Schedule $schedule)
{
    $schedule->command('imageable:prune')
        ->onOneServer()
        ->daily();
}
```
