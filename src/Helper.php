<?php

  namespace Xparse\ElementFinder;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 6/3/14
   */
  class Helper {

    /**
     * @param \DOMNode $node
     * @param bool $isHtml
     * @return string
     */
    public static function getOuterHtml(\DOMNode $node, $isHtml = true) {

      if ($isHtml) {
        $saveMethod = 'saveHtml';
      } else {
        $saveMethod = 'saveXml';
      }

      $domDocument = new \DOMDocument('1.0');
      $b = $domDocument->importNode($node->cloneNode(true), true);
      $domDocument->appendChild($b);

      $html = $domDocument->$saveMethod();
      $html = static::safeEncodeStr($html);

      return $html;
    }


    /**
     * @param \DOMNode $itemObj
     * @return string
     */
    public static function getInnerHtml(\DOMNode $itemObj) {
      $innerHtml = '';
      $children = $itemObj->childNodes;
      foreach ($children as $child) {
        $innerHtml .= $child->ownerDocument->saveXML($child);
      }
      $innerHtml = static::safeEncodeStr($innerHtml);
      return $innerHtml;
    }

    /**
     * Simple helper function for str encoding
     *
     * @param string $str
     * @return string
     */
    public static function safeEncodeStr($str) {
      return preg_replace_callback("/&#([a-z\d]+);/i", function ($m) {
        $m[0] = (string) $m[0];
        $m[0] = mb_convert_encoding($m[0], "UTF-8", "HTML-ENTITIES");
        return $m[0];
      }, $str);
    }

    /**
     * @param string $regex
     * @param integer|callable $i
     * @param array $strings
     * @return ElementFinder\StringCollection
     * @throws \Exception
     */
    public static function match($regex, $i, $strings = array()) {

      if (!is_callable($i) and !is_numeric($i)) {
        throw new \InvalidArgumentException('Expect integer or callback');
      }

      $items = new \Xparse\ElementFinder\ElementFinder\StringCollection();

      foreach ($strings as $string) {

        if (!preg_match_all($regex, $string, $matchedData)) {
          continue;
        }

        if (is_int($i)) {

          if (!isset($matchedData[$i])) {
            continue;
          }

          foreach ($matchedData[$i] as $resultString) {
            $items[] = $resultString;
          }
          continue;
        }

        # callback function
        $rawStringResult = $i($matchedData);
        if (!is_array($rawStringResult)) {
          throw new \Exception("Invalid value. Expect array from callback");
        }

        foreach ($rawStringResult as $resultString) {
          $items[] = $resultString;
        }
      }

      return $items;

    }

  } 