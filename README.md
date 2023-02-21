# Laravel Import Reader

Laravel bridge for `dmt-software/import-reader` to iterate over the contents of
(huge) imports.

## Support

This package is tested on and requires laravel >= 8.0.

## Installation

`composer require dmt-software/laravel-import-reader`

The service provider can be automatically registered by auto-discovery. 
Otherwise, register it manually add it to _config/app.php_:

```php
'providers' => [
    // ...
    DMT\Laravel\Import\Reader\Providers\ImportReaderServiceProvider::class,
];
```

To publish the configuration file:

`php artisan vendor:publish --provider="DMT\Laravel\Import\Reader\Providers\ImportReaderServiceProvider"`

## Configuration

The extensions entry within the configuration can be used to autodetect a 
_readerHandler_ by mapping the file extension to the requested handler:

```php
return [
    'extensions' => [
        'cxml' => DMT\Import\Reader\Handlers\XmlReaderHandler::class,     
    ],
];
```
By default json, xml and csv are autodetected. 

### Customization

The configuration also has entries for custom sanitizers, handlers and a custom
error handler. See the _config/reader.php_ file for their usage or the 
`dmt-software/import-reader` documentation.


## Usage

A file called _items.json_ contains the following data:

```js
[
  { 
    "id": 1,
    "name": "item-name",
    // ...
  },
  {
    "id": 2,
    "name": "item2-name",
    // ...
  },
  // ...
]
```

That can be read into chunks that can be imported into a database:

```php
use DMT\Laravel\Import\Reader\Facades\ImportReader;

/** @var iterable<int, array> $items */
$items = ImportReader::buildToArrayReader(
    'directory-to/items.json', [
        'path' => '.'
    ])->read();

foreach ($items as $row => $item) {
    // process the items 
    if (!empty($item['name'])) {
        Item::updateOrCreate($item);
    }
}
```

Or the reader builder can be injected into your classes or methods.

```php
use DMT\Import\Reader\ReaderBuilder;
use Illuminate\Console\Command;

class MyImportCommand extends Command
{
    protected $signature = 'import:items {file}';
    
    public function handle(ReaderBuilder $builder)
    {
        $reader = $builder->build($this->argument('file'), []);
        
        foreach ($reader->read($this->argument('skip')) as $item) {
            // process item
        }
    }
}
```
