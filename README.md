## Laravel Email Template

 copy database/migrations/2016_01_10_023406_create_email_templates_table.php to [laravel base path]/database/migrations/

 ```
 artisan migrate
 ```

```php
\Recca0120\EmailTemplate\EmailTemplate::create([
    'slug' => 'my first template'
    'from_address' => 'admin@admin.com',
    'from_name' => 'admin',
    'content' => 'my name is: {{$name}}'
]);

$mailer = app()->make(\Recca0120\EmailTemplate\Mailer::class);
$slug = 'my first template';
$mailer->send($slug, ['name' => 'recca0120'], function($m) {
    $m->to('recca0120@gmail.com');
});
```
