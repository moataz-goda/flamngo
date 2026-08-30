<?php

namespace App\Support;

trait HasTranslations
{
    public function t(string $field): mixed
    {
        if (app()->getLocale() === 'en') {
            $en = $field.'_en';
            if (array_key_exists($en, $this->getAttributes()) || isset($this->{$en})) {
                $value = $this->{$en};
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        return $this->{$field};
    }
}
