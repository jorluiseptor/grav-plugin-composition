i use this to filter tags
```php
    public function inArray($key, array $values): NewsCollection
    {
        $mappedCollection = $this->map(function($obj) use ($values, $key) {
            foreach ($values as $value) {
                if (in_array($value, $obj->getProperty($key, []))) {
                    return $obj;
                }
            }
            return false; // if element doesnt match what we want, make it false
        });


        $array_with_elements_remove = array_filter($mappedCollection->getElements(), function($e){
            return $e; //remove all the unwanted elements that are false
        });

        return $this->createFrom(array_values($array_with_elements_remove));
    }
```

i call it this way

```php
$collection = new NewsCollection($events, $flex->getDirectory('news'));

if ($uri->param('tag')) {
  return $collection->inArray('tags', [$uri->param('tag')]);
}
```

you could also call it in twig the same way
`{% set collection = collection.inArray('tags', [uri.param('tag')] %}`