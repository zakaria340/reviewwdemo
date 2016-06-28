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

        //listen presta_sitemap.populate event
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
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
        });
    }
}
