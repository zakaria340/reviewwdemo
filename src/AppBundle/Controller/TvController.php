<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sunra\PhpSimple\HtmlDomParser;
use AppBundle\Entity\Item;
use AppBundle\Entity\Urls;

class TvController extends Controller {

  /**
   * @Route(
   *     "/watch-tv-show/{page}",
   *     name="tvshow",
   *    defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function tvshowAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getTvApi()->getPopular(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'tvshow',
      'pages_count' => 10,//$TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Tv:tvshow.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination
    ));
  }

  /**
   * @Route(
   *     "/watch-tv-show/{id}-{slug}/",
   *     name="viewtvshow"
   * )
   */
  public function viewtvshowAction($slug, $id) {

    $movie = $this->get('tmdb.tv_repository')->load($id);
    $crew = $movie->getCredits()->getCrew();
    $castList = $movie->getCredits()->getCast();
    $listMovies = $movie->getSimilar();
    $listSeasons = $movie->getSeasons();
    $crewList = array();
    foreach ($crew as $key => $value) {
      $crewList[$value->getJob()][] = $value;
    }
    return $this->render('AppBundle:Tv:viewtvshow.html.twig', array('movie' => $movie,
          'crewList' => $crewList,
          'castList' => $castList,
          'listMovies' => $listMovies,
          'listSeasons' => $listSeasons
            )
    );
  }

  /**
   * @Route(
   *     "/watch-tv-show/{id}-{slug}/season/{idseason}/",
   *     
   *     name="viewseasonshow"
   * )
   */
  public function viewseasonshowAction($slug, $id, $idseason) {
    $movie = $this->get('tmdb.tv_season_repository')->load($id, $idseason);
    $idSais = $movie->getId();
    $tvshow = $this->get('tmdb.tv_repository')->load($id);
    $listEpisodes = $movie->getEpisodes();

    $title = $tvshow->getName() . ' ' . $idseason;
    $title = str_replace(' ', '+', $title);
    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $idSais));
    if (empty($itemEntity)) {
      $listUrlsVideo = $this->getUrlsMovies($title);
      if (!empty($listUrlsVideo)) {
        $em = $this->getDoctrine()->getManager();
        $item = new Item();
        $item->setIdApi($idSais);
        $em->persist($item);
        $em->flush();
        foreach ($listEpisodes as $ep) {
          $epNumber = $ep->getEpisodeNumber();
          $epNumber = sprintf('%02d', $epNumber);
          if (isset($listUrlsVideo[$epNumber])) {
            $this->SaveUrlsMovies($listUrlsVideo[$epNumber], $ep->getId());
          }
        }
      }
    }

    $listImages = $movie->getImages();
    return $this->render('AppBundle:Tv:viewseasonshow.html.twig', array(
          'movie' => $movie,
          'tvshow' => $tvshow,
          'listEpisodes' => $listEpisodes,
          'listImages' => $listImages,
          'idTv' => $id
            )
    );
  }

  /**
   * @Route(
   *     "/watch-tv-show/{id}-{slug}/season/{idseason}/episode/{idepisode}/",
   *     name="viewepisodeshow"
   * )
   */
  public function viewepisodeshowAction($slug, $id, $idseason, $idepisode) {
    $movie = $this->get('tmdb.tv_episode_repository')->load($id, $idseason, $idepisode);
    $idEpis = $movie->getId();
    $listImages = $movie->getImages();
    $tvshow = $this->get('tmdb.tv_repository')->load($id);
    $season = $this->get('tmdb.tv_season_repository')->load($id, $idseason);

    $title = $tvshow->getName() . ' ' . $idseason;
    $title = str_replace(' ', '+', $title);
    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $idEpis));
    $listUrlsVideo = array();
    if (empty($itemEntity)) {
      $listUrlsVideo = $this->getUrlsMovies($title);
      if (!empty($listUrlsVideo)) {
        $em = $this->getDoctrine()->getManager();
        $item = new Item();
        $item->setIdApi($idseason);
        $em->persist($item);
        $em->flush();

        foreach ($listEpisodes as $ep) {
          $epNumber = $ep->getEpisodeNumber();
          $epNumber = sprintf('%02d', $epNumber);
          if (isset($listUrlsVideo[$epNumber])) {
            $this->SaveUrlsMovies($listUrlsVideo[$epNumber], $ep->getId());
          }
        }
      }
    }
    else {
      $listUrls = $em->getRepository('AppBundle:Urls')
          ->findBy(array('item' => $itemEntity));
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

    return $this->render('AppBundle:Tv:viewepisodeshow.html.twig', array(
          'movie' => $movie,
          'tvshow' => $tvshow,
          'season' => $season,
          'listImages' => $listImages,
          'listUrls' => $listUrlsVideo
            )
    );
  }

  public function getUrlsMovies($title) {
      $urlSearch = 'http://fmovies.to/search?keyword=' . $title;
      $dom = HtmlDomParser::file_get_html($urlSearch);
         var_dump($urlSearch);
    var_dump($dom);die;
    if(!$dom){
        return array();
      }
      $hrefIframedata = $dom->find('.movie-list .item', 0);
    $listUrlsVideo = array();
    if ($hrefIframedata) {
        $hrefmovie = $hrefIframedata->find('a', 0)->href;
        $urlToParse = 'http://fmovies.to' . $hrefmovie;
      $dom = HtmlDomParser::file_get_html($urlToParse);
      $i = 0;
      foreach ($dom->find('#servers .server ') as $div) {
        if ($div->attr['data-type'] == 'iframe') {
          foreach ($div->find('.episodes li ') as $li) {
            $i++;
            $hrefIframedata = $li->find('a', 0)->attr['data-id'];
            $episodNumber = $li->find('a', 0)->plaintext;
            $episodNumber = sprintf('%02d', $episodNumber);
            if (!isset($listUrlsVideo[$episodNumber])) {
              $listUrlsVideo[$episodNumber] = array();
            }
            $hrefIframequalite = $li->find('a', 0)->plaintext;
            $hrefIframe = 'http://fmovies.to/ajax/episode/info?id=' . $hrefIframedata;
            $dom = file_get_contents($hrefIframe);
            $dom = json_decode($dom);
            $parse = parse_url($dom->target);

            $listUrlsVideo[$episodNumber][] = array(
              'url' => $dom->target,
              'name' => $parse['host'],
              'qualite' => $hrefIframequalite,
              'id' => $i,
              'type' => 'iframe',
              'host' => $parse['host']
            );
            sleep(3);
          }
        }
      }
    }
    return $listUrlsVideo;
  }

  /**
   * 
   * @param type $listUrlMovies
   * @param type $id
   */
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
