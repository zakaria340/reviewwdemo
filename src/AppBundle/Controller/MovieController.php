<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sunra\PhpSimple\HtmlDomParser;
use AppBundle\Entity\Item;
use AppBundle\Entity\Urls;

class MovieController extends Controller {

  /**
   * @Route(
   *     "/watch-movies/{page}",
   *     name="popularMovies",
   *     options={"sitemap" = {"priority" = 0.7, "changefreq" = "weekly" }},
   *     defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function popularmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getPopular(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'popularMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:popularmovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination
    ));
  }

  /**
   * @Route("/top-rated-movies/{page}", name="topratedMovies","sitemap" = {"priority" = 0.7, "changefreq" = "weekly" }, defaults={"page" = 1})
   */
  public function topratedmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getTopRated(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'topratedMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:topratedmovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination
    ));
  }

  /**
   * @Route("/updatabase", name="updatabase")
   */
  public function updatabaseAction() {
    for ($i = 13; $i < 20; $i++) {
      $urlSearch = 'http://fmovies.to/movies?page=' . $i;
      $dom = HtmlDomParser::file_get_html($urlSearch);
      foreach ($dom->find('.movie-list .item') as $element) {
        sleep(3);
        $hrefmovie = $element->find('a', 0)->href;
        $urlToParse = 'http://fmovies.to' . $hrefmovie;
        $dom = HtmlDomParser::file_get_html($urlToParse);

        $title_movie = $dom->find('h1.name', 0)->plaintext;
        $client = $this->get('tmdb.client');
        $search = $client->getSearchApi()->searchMovies($title_movie);
        if (!empty($search['results'])) {
          $firstMovie = $search['results'][0];
          $idMovie = $firstMovie['id'];
              $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $idMovie));
    if (empty($itemEntity)) {
          $i = 0;
          $listUrlsVideo = array();
          foreach ($dom->find('#servers .server ') as $div) {
            if ($div->attr['data-type'] == 'iframe') {
              $i++;
              $hrefIframedata = $div->find('a', 0)->attr['data-id'];
              $hrefIframequalite = $div->find('a', 0)->plaintext;
              $hrefIframe = 'http://fmovies.to/ajax/episode/info?id=' . $hrefIframedata;
              sleep(2);
              $dom = file_get_contents($hrefIframe);
              $dom = json_decode($dom);
              $parse = parse_url($dom->target);
              $listUrlsVideo[] = array(
                'url' => $dom->target,
                'name' => $parse['host'],
                'qualite' => $hrefIframequalite,
                'id' => $i,
                'type' => 'iframe',
                'host' => $parse['host']
              );
            }
          }
          $this->SaveUrlsMovies($listUrlsVideo, $idMovie);
             }
        }
      }
    }
  }

  /**
   * @Route("/now-playing-movies/{page}", name="nowplayingMovies","sitemap" = {"priority" = 0.7, "changefreq" = "weekly" }, defaults={"page" = 1})
   */
  public function nowplayingmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getNowPlaying(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'nowplayingMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:nowplayingmovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination
    ));
  }

  /**
   * @Route("/up-coming-movies/{page}", name="upcomingMovies","sitemap" = {"priority" = 0.7, "changefreq" = "weekly" }, defaults={"page" = 1})
   */
  public function upcomingmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getUpcoming(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'upcomingMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:upcomingmovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination
    ));
  }

  /**
   * @Route(
   *     "/watch-movies/{id}-{slug}/",
   *     name="viewmovie",
   *     "sitemap" = {"priority" = 0.4, "changefreq" = "weekly" }
   * )
   */
  public function viewAction($slug, $id) {
    $movie = $this->get('tmdb.movie_repository')->load($id);
    $title = str_replace(' ', '+', $movie->getTitle());
    $imdbId = $movie->getImdbId();
    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $id));

    $listUrls = [];
    if (!is_null($itemEntity)) {
      $listUrls = $em->getRepository('AppBundle:Urls')
          ->findBy(array('item' => $itemEntity));
      $listUrlsVideo = array();
      foreach ($listUrls as $url) {
        $listUrlsVideo[] = array(
          'url' => $url->getUrl(),
          'name' => $url->getName(),
          'qualite' => $url->getQualite(),
          'id' => $url->getId(),
          'type' => $url->getType(),
          'host' => $url->getHost()
        );
      }
    }
    else {
      $listUrlsVideo = $this->getUrlsMovies($title, $imdbId);
      $this->SaveUrlsMovies($listUrlsVideo, $id);
    }
    $crew = $movie->getCredits()->getCrew();
    $cast = $movie->getCredits()->getCast();
    $listMovies = $movie->getSimilar();
    $listImages = $movie->getImages();
    $castList = $cast;
    $crewList = array();
    foreach ($crew as $key => $value) {
      $crewList[$value->getJob()][] = $value;
    }


    return $this->render('AppBundle:Movie:view.html.twig', array(
          'movie' => $movie,
          'crewList' => $crewList,
          'castList' => $castList,
          'listMovies' => $listMovies,
          'listImages' => $listImages,
          'listUrls' => $listUrlsVideo
            )
    );
  }

  public function getUrlsMovies($title, $imdbId) {
    $urlSearch = 'http://fmovies.to/search?keyword=' . $title;
    $dom = HtmlDomParser::file_get_html($urlSearch);
    $listUrlsVideo = array();

    if ($dom) {


      $hrefIframedata = $dom->find('.movie-list .item', 0);
      if ($hrefIframedata) {
        $hrefmovie = $hrefIframedata->find('a', 0)->href;
        $urlToParse = 'http://fmovies.to' . $hrefmovie;
        $dom = HtmlDomParser::file_get_html($urlToParse);
        $i = 0;
        foreach ($dom->find('#servers .server ') as $div) {
          if ($div->attr['data-type'] == 'iframe') {
            $i++;
            $hrefIframedata = $div->find('a', 0)->attr['data-id'];
            $hrefIframequalite = $div->find('a', 0)->plaintext;
            $hrefIframe = 'http://fmovies.to/ajax/episode/info?id=' . $hrefIframedata;
            $dom = file_get_contents($hrefIframe);
            $dom = json_decode($dom);
            $parse = parse_url($dom->target);
            $listUrlsVideo[] = array(
              'url' => $dom->target,
              'name' => $parse['host'],
              'qualite' => $hrefIframequalite,
              'id' => $i,
              'type' => 'iframe',
              'host' => $parse['host']
            );
          }
        }
      }

      /**
       * 
       */
      $urlToParse = 'http://www.seehd.club/?s=' . $title;
      $dom2 = HtmlDomParser::file_get_html($urlToParse);
      if ($dom2->find('#content .article-helper h2 a', 0)) {
        $href = $dom2->find('#content .article-helper h2 a', 0)->href;

        $dom = HtmlDomParser::file_get_html($href);
        $urlUpload = $dom->find('.entry-content iframe', 0)->src;

        $parse = parse_url($urlUpload);
        $listUrlsVideo[] = array(
          'url' => $urlUpload,
          'name' => 'Upload',
          'qualite' => 'HD',
          'id' => 3,
          'type' => 'iframe',
          'host' => $parse['host']
        );
      }
    }
    return $listUrlsVideo;
  }

  public function SaveUrlsMovies($listUrlMovies, $id) {

    $em = $this->getDoctrine()->getManager();
    $item = new Item();
    $item->setIdApi($id);
    foreach ($listUrlMovies as $url) {
      $urls = new Urls();
      $urls->setName($url['name']);
      $urls->setUrl($url['url']);
      $urls->setQualite($url['qualite']);
      $urls->setType($url['type']);
      $urls->setHost($url['host']);
      $urls->setItem($item);
      $em->persist($urls);
    }

    $em->persist($item);
    $em->flush();
  }

}
