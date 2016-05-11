# table_read

A compact PHP function for reading items from an HTML table.

## Usage

### Read the items one at a time

```php
foreach (table_read('input.html') as $data) {
  // do something with $data (each row of the first <table>)
}
```

### Specify a selector for the table

```php
foreach (table_read('input.html', '//table[@id="foo"]') as $data) {
  // do something with $data
}
```

### Read all the items into an array

```php
$items = iterator_to_array(table_read('input.html'));
```
