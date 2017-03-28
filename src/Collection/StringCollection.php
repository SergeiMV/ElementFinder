<?php

  declare(strict_types=1);

  namespace Xparse\ElementFinder\Collection;

  use Xparse\ElementFinder\Helper\RegexHelper;

  /**
   * @author Ivan Shcherbak <alotofall@gmail.com>
   */
  class StringCollection implements \IteratorAggregate, \Countable {

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Array of strings
     *
     * @var string[]
     */
    protected $items = [];


    /**
     * @param string[] $items
     * @throws \Exception
     */
    public function __construct(array $items = []) {
      $this->setItems($items);
    }


    public function __clone() {
      $items = [];
      foreach ($this->items as $item) {
        $items[] = $item;
      }
      $this->setItems($items);
    }


    /**
     * Return number of items in this collection
     *
     * @return int
     */
    public function count() : int {
      return count($this->items);
    }


    /**
     * Add one item to the end of collection
     * This item is accessible via `$collection->getLast();`
     *
     * @param mixed $item
     * @return self
     * @throws \Exception
     */
    public function append($item) : self {
      $this->validateType($item);
      $this->items[] = $item;
      return $this;
    }


    /**
     * Truncate current list of items and add new
     * @param string[] $items
     * @return self
     * @throws \Exception
     */
    public function setItems(array $items) : self {
      foreach ($items as $key => $item) {
        $this->validateType($item);
      }
      $this->items = $items;
      return $this;
    }


    /**
     * Return last item from collection
     *
     * @return null|string
     */
    public function getLast() {
      if ($this->count() === 0) {
        return null;
      }
      return end($this->items);
    }


    /**
     * Return first item from collection
     *
     * @return null|string
     */
    public function getFirst() {
      if ($this->count() === 0) {
        return null;
      }
      return reset($this->items);
    }


    /**
     * Return array of items connected to this collection
     *
     * Rewrite this method in you class
     *
     * <code>
     * foreach($collection->getItems() as $item){
     *  echo get_class($item)."\n;
     * }
     * </code>
     * @return string[]
     */
    public function getItems() : array {
      return $this->items;
    }


    /**
     * Iterate over objects in collection
     *
     * <code>
     * $collection->walk(function(string $item, int $index, StringCollection $collection){
     *    echo $item;
     * })
     * </code>
     * @param callable $callback
     * @return self
     */
    public function walk(callable $callback) : self {
      foreach ($this->getItems() as $index => $item) {
        $callback($item, $index, $this);
      }
      return $this;
    }


    /**
     * @deprecated
     * @see walk
     *
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback) : self {
      $this->walk($callback);
      return $this;
    }


    /**
     * You can add or append only one type of items to this collection
     *
     * @param string|float|int $item
     * @return bool
     * @throws \Exception
     */
    private function validateType($item) : bool {
      if (is_string($item) or is_float($item) or is_int($item)) {
        return true;
      }
      throw new \InvalidArgumentException('Expect string');
    }


    /**
     * @param int $index
     * @return string
     */
    public function item(int $index) : string {
      if (isset($this->items[$index])) {
        return $this->items[$index];
      }
      return '';
    }


    /**
     * @param string $regexp
     * @param string $to
     * @return self
     */
    public function replace(string $regexp, string $to = '') : self {
      foreach ($this->items as $index => $item) {
        $this->items[$index] = preg_replace($regexp, $to, $item);
      }

      return $this;
    }


    /**
     * Match strings and return new collection
     * @param string $regexp
     * @param int $index
     * @return StringCollection
     * @throws \Exception
     */
    public function match(string $regexp, int $index = 1) : StringCollection {
      return RegexHelper::match($regexp, $index, $this->items);
    }


    /**
     * Split strings by regexp
     * @param string $regexp
     * @return StringCollection
     * @throws \Exception
     */
    public function split(string $regexp) : StringCollection {
      $items = new StringCollection();

      foreach ($this->items as $item) {
        $data = preg_split($regexp, $item);
        foreach ($data as $string) {
          $items->append($string);
        }
      }

      return $items;
    }


    /**
     * @return StringCollection
     */
    public function unique() : StringCollection {
      return new StringCollection(array_unique($this->items));
    }


    public final function merge(StringCollection $collection) : StringCollection {
      return new StringCollection(array_merge($this->getItems(), $collection->getItems()));
    }


    /**
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return string[]|\ArrayIterator An instance of an object implementing Iterator or Traversable
     */
    public function getIterator() {
      return new \ArrayIterator($this->items);
    }

  }