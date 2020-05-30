<?php

namespace App\Util;

class TemplateVariableExtraction
{

  public static function Extract(string $filePath): array
  {
    $vars = [];

    $matches = [];
    $fileContent = file_get_contents($filePath);
    preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $fileContent, $matches);

    // Clean up matches.
    TemplateVariableExtraction::Normalize($matches, $vars);

    // Remove duplicates.
    // $vars = array_unique($vars);

    return $vars;
  }

  public static $inForLoop = false;

  public static function Normalize(array $matches, array &$vars)
  {
    // Clean up matches.
    for ($i = 0; $i < count($matches); $i++) {
      if (gettype($matches[$i]) == 'string' && (substr($matches[$i], 0, 2) == '{{' || substr($matches[$i], 0, 2) == '{%')) {
        $varName = trim(substr($matches[$i], 2, strlen($matches[$i]) - 4));
        // Check if its a for loop.
        if (TemplateVariableExtraction::$inForLoop == false) {
          if (substr($varName, 0, 3) == 'for') {
            $varName = preg_replace('/for\s*.*?\s*in\s*/i', '', $varName, 1);
            $vars[$varName] = ['type' => 'array'];
            TemplateVariableExtraction::$inForLoop = true;
          } else {
            $vars[$varName] = ['type' => 'string'];
          }
        } else {
          if (substr($varName, 0, 6) == 'endfor') {
            TemplateVariableExtraction::$inForLoop = false;
          }
        }

      } else if (gettype($matches[$i]) == 'array') {
        TemplateVariableExtraction::Normalize($matches[$i], $vars);
      }
    }
  }
}
