<?php
class ValidationError extends Exception {}

function str_between(string $name, string $value=null, int $min=1, int $max=255): string {
  $value = trim((string)$value);
  if ($value === '' || mb_strlen($value) < $min || mb_strlen($value) > $max) {
    throw new ValidationError("$name 길이 오류");
  }
  return $value;
}

function matches(string $name, string $value=null, string $pattern='/^[\pL\pN _\-\.]+$/u'): string {
  $value = trim((string)$value);
  if (!preg_match($pattern, $value)) throw new ValidationError("$name 형식 오류");
  return $value;
}