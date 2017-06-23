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
      'page'         => $page,
      'route'        => 'tvshow',
      'pages_count'  => 150, //$TopRatedMovies['total_pages'],
      'route_params' => array(),
    );
    return $this->render(
      'AppBundle:Tv:tvshow.html.twig', array(
        'movies'     => $TopRatedMovies,
        'pagination' => $pagination,
      )
    );
  }

  /**
   * @Route(
   *     "/watch-tv-show/{id}-{slug}/",
   *     name="viewtvshow"
   * )
   */
  public function viewtvshowAction($slug, $id) {

    $movie = $this->get('tmdb.tv_repository')->load($id);
    if (empty($movie)) {
      return $this->redirect($this->generateUrl('homepage'));
    }
    $crew = $movie->getCredits()->getCrew();
    $castList = $movie->getCredits()->getCast();
    $listMovies = $movie->getSimilar();
    $listSeasons = $movie->getSeasons();
    $crewList = array();
    foreach ($crew as $key => $value) {
      $crewList[$value->getJob()][] = $value;
    }
    return $this->render(
      'AppBundle:Tv:viewtvshow.html.twig', array(
        'movie'       => $movie,
        'crewList'    => $crewList,
        'castList'    => $castList,
        'listMovies'  => $listMovies,
        'listSeasons' => $listSeasons,
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
    if (empty($movie)) {
      return $this->redirect($this->generateUrl('homepage'));
    }
    $idSais = $movie->getId();
    $tvshow = $this->get('tmdb.tv_repository')->load($id);
    $listEpisodes = $movie->getEpisodes();

    $title = $tvshow->getName() . ' season ' . $idseason;
    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(
      array('idApi' => $idSais)
    );
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
          if (isset($listUrlsVideo[$epNumber])) {
            $this->SaveUrlsMovies($listUrlsVideo[$epNumber], $ep->getId());
          }
        }
      }
    }

    $listImages = $movie->getImages();
    return $this->render(
      'AppBundle:Tv:viewseasonshow.html.twig', array(
        'movie'        => $movie,
        'tvshow'       => $tvshow,
        'listEpisodes' => $listEpisodes,
        'listImages'   => $listImages,
        'idTv'         => $id,
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
    if(empty($movie)) {
      return $this->redirect($this->generateUrl('homepage'));
    }
    $idEpis = $movie->getId();
    $listImages = $movie->getImages();
    $tvshow = $this->get('tmdb.tv_repository')->load($id);
    $season = $this->get('tmdb.tv_season_repository')->load($id, $idseason);
    $listEpisodes = $season->getEpisodes();
    $title = $tvshow->getName() . ' season ' . $idseason;
    //$title = str_replace(' ', '+', $title);
    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $idEpis));
    $listUrlsVideo = array();
    if (empty($itemEntity)) {
      $listUrlsVideo = $this->getUrlsMovies($title);
      if (!empty($listUrlsVideo)) {
        $itemEntityA = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $idseason));
        if(empty($itemEntityA)){
          $em = $this->getDoctrine()->getManager();
          $item = new Item();
          $item->setIdApi($idseason);
          $em->persist($item);
          $em->flush();

        }

        foreach ($listEpisodes as $ep) {
          $epNumber = $ep->getEpisodeNumber();
          //$epNumber = sprintf('%02d', $epNumber);
          if (isset($listUrlsVideo[$epNumber])) {
            $this->SaveUrlsMovies($listUrlsVideo[$epNumber], $ep->getId());
          }
        }
      }
    }
    else {
      $listUrls = $em->getRepository('AppBundle:Urls')
        ->findBy(array('item' => $itemEntity));
      if (empty($listUrls)) {
        $listUrlsVideo = $this->getUrlsMovies($title);
        if (!empty($listUrlsVideo)) {
          foreach ($listEpisodes as $ep) {
            $epNumber = $ep->getEpisodeNumber();
            //$epNumber = sprintf('%02d', $epNumber);
            if (isset($listUrlsVideo[$epNumber])) {
              $this->SaveUrlsMovies($listUrlsVideo[$epNumber], $ep->getId());
            }
          }
        }
      }
      else {
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
    }
    return $this->render('AppBundle:Tv:viewepisodeshow.html.twig', array(
        'movie' => $movie,
        'tvshow' => $tvshow,
        'season' => $season,
        'listImages' => $listImages,
        'listUrls' => $listUrlsVideo,
        'listEpisodes' => $listEpisodes,
        'idTv' => $id
      )
    );
  }

  public function getUrlsMovies($title) {
    sleep(1);
    $url = 'https://gomovies.to/ajax/suggest_search';
    $fields = array(
      'keyword' => urlencode($title),
    );
    $fields_string = '';
    //url-ify the data for the POST
    foreach ($fields as $key => $value) {
      $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_POST, count($fields));
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Google');
    $query = curl_exec($curl_handle);
    curl_close($curl_handle);
    $dom = HtmlDomParser::str_get_html($query);
    if(is_null($dom->find('a', 1))) {
      return [];
    }
    $urlMovie = $dom->find('a', 1)->attr['href'];
    $urlMovie = stripslashes($urlMovie);
    $urlMovie = str_replace('/"n', '', $urlMovie);
    $urlMovie = str_replace('"', '', $urlMovie);
    $urlMovie = explode('-', $urlMovie);
    $idMovie = end($urlMovie);
    $urlMovie = explode('.', $idMovie);
    $listMovie = 'https://gomovies.to/ajax/v2_get_episodes/' . $urlMovie[0];
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $listMovie);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Google');
    $query = curl_exec($curl_handle);
    curl_close($curl_handle);
    $dom = HtmlDomParser::str_get_html($query);
    $i = 0;
    $listUrlsVideo = array();
    foreach ($dom->find('#server-14 .les-content a') as $div) {

      $i++;
      $episodeId = $div->attr['episode-id'];
      $episodeId = trim($episodeId);

      $geturl = "https://gomovies.to/ajax/load_embed/$episodeId";
      $curl_handle = curl_init();
      curl_setopt($curl_handle, CURLOPT_URL, $geturl);
      curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
      curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Google');
      $query = curl_exec($curl_handle);
      curl_close($curl_handle);
      $json_decoded = json_decode(stripslashes($query));

      if (!is_null($json_decoded)) {
        $parse = parse_url($json_decoded->embed_url);
        $listUrlsVideo[$i][] = array(
          'url'     => $json_decoded->embed_url,
          'name'    => $parse['host'],
          'qualite' => "TVRIP",
          'id'      => $i,
          'type'    => 'iframe',
          'host'    => $parse['host'],
        );
      }
    }
    return $listUrlsVideo;
  }

  /**
   *
   * @param type $listUrlMovies
   * @param type $id
   */
  public function SaveUrlsMovies($listUrlMovies, $id, $save = TRUE) {
    $em = $this->getDoctrine()->getManager();

    $repository = $this
      ->getDoctrine()
      ->getManager()
      ->getRepository('AppBundle:Item');
    $itemE = $repository->findOneBy(array('idApi' => $id));
    if ($itemE) {
      $item = $itemE;
    }
    else {
      $item = new Item();
      $item->setIdApi($id);
    }


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
