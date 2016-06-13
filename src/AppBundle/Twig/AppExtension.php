<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('GenerateSlug', array($this, 'slugFilter')),
        );
    }

    public function slugFilter($title)
    {
     return $this->slug($title, '-');
    }

    public function getName()
    {
        return 'app_extension';
    }
      public function slug($str, $char) {

        // Lower case the string and remove whitespace from the beginning or end
        $str = trim(strtolower($str));

        // Remove single quotes from the string
        $str = str_replace('', '', $str);

        // Every character other than a-z, 0-9 will be replaced with a single dash (-)
        $str = preg_replace('/[^a-z0-9]+/', $char, $str);

        // Remove any beginning or trailing dashes
        $str = trim($str, $char);

        return $str;
    }
}