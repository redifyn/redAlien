# redAlien Polished Bootstrap 5 UI

This package contains drop-in MVC view/layout files for your redAlien project.

## Copy these folders into your project

- `app/views/layouts/`
- `app/views/dashboard/index.php`
- `public/assets/css/redalien.css`
- `public/assets/js/redalien.js`
- `public/assets/images/`

Your `Controller::view()` should load files in this order:

```php
require APPROOT . '/views/layouts/header.php';
require APPROOT . '/views/layouts/navbar.php';
require APPROOT . '/views/layouts/sidebar.php';
require $viewFile;
require APPROOT . '/views/layouts/footer.php';
```

Your `BASE_URL` should point to:

```text
http://localhost/redAlien/public
```

Then open:

```text
http://localhost/redAlien/public
```

The links are visual placeholders for now. They can be connected to real routes later.
