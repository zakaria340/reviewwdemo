<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
class AppBundle extends Bundle
{
   public function boot()
    {
        $router = $this->container->get('router');
        $event  = $this->container->get('event_dispatcher');
        $client = $this->container->get('tmdb.client');
        //listen presta_sitemap.populate event
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router, $client){
                //get absolute homepage url
                $url = $router->generate('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL);
                //add homepage url to the urlset named default
                $event->getUrlContainer()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_HOURLY,
                        1
                    ),
                    'default'
                );
                for($page=1;$page<=800;$page++){
                  
                       $TopRatedMovies = $client->getMoviesApi()->getPopular(array('page' => $page));
                    foreach ($TopRatedMovies['results'] as $movie) {
                         // Lower case the string and remove whitespace from the beginning or end
        $str = trim(strtolower($movie['title']));

        // Remove single quotes from the string
        $str = str_replace('', '', $str);

        // Every character other than a-z, 0-9 will be replaced with a single dash (-)
        $str = preg_replace('/[^a-z0-9]+/', '', $str);

        // Remove any beginning or trailing dashes
        $str = trim($str, '');

            $url1 = $router->generate('viewmovie', array('id' => $movie['id'], 'slug' => $str), UrlGeneratorInterface::ABSOLUTE_URL);
            $event->getUrlContainer()->addUrl(
              new UrlConcrete(
              $url1, new \DateTime(), UrlConcrete::CHANGEFREQ_DAILY, 1
              ), 'default'
            );
          }
                }
           
        });
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
