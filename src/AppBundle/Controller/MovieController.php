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
      'pages_count' => 150, //$TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:popularmovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination
    ));
  }

  /**
   * @Route(
   *     "/movies/tag/{tag}",
   *     name="moviestag",
   *     defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function moviestagAction($tag) {
    $page = rand(2, 5);
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getTopRated(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'popularMovies',
      'pages_count' => 1, //$TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:tagmovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination,
          'tag' => $tag,
    ));
  }

  /**
   * @Route(
   *     "/watch-movies/genre/{idgenre}-{genre}/{page}",
   *     name="genreMovies",
   *     defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function moviesgenreAction($genre, $idgenre, $page) {
    $repository = $this->get('tmdb.genre_repository');
    $TopRatedMovies = $repository->getMovies($idgenre, array('page' => $page));
    $genreData = $repository->load($idgenre);
    $pagination = array(
      'page' => $page,
      'route' => 'genreMovies',
      'pages_count' => 150, //$TopRatedMovies['total_pages'],
      'route_params' => array('genre' => $genre, 'idgenre' => $idgenre)
    );
    return $this->render('AppBundle:Movie:genremovies.html.twig', array(
          'movies' => $TopRatedMovies,
          'pagination' => $pagination,
          'genre' => $genreData,
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
   * @Route(
   *     "/watch-movies/{id}-{slug}/",
   *      name="viewmovie"
   * )
   */
  public function viewAction($slug, $id) {
    $movie = $this->get('tmdb.movie_repository')->load($id);
    if(empty($movie)) {
      return $this->redirect($this->generateUrl('homepage'));
    }
    $title = $movie->getTitle();
    $imdbId = $movie->getImdbId();
    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $id));

    $listUrls = [];
    if (!empty($itemEntity)) {
      $listUrls = $em->getRepository('AppBundle:Urls')
          ->findBy(array('item' => $itemEntity));
      $listUrlsVideo = array();
      if (empty($listUrls)) {
        $listUrlsVideo = $this->getUrlsMovies($title, $imdbId);
        $this->SaveUrlsMovies($listUrlsVideo, $id, FALSE);
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


    $listTags = array(
      'watch', 'watch movies', 'watch online', 'online', 'online movies', 'watch movies online', 'watch online free', 'online movies', 'online');
    return $this->render('AppBundle:Movie:view.html.twig', array(
          'movie' => $movie,
          'crewList' => $crewList,
          'castList' => $castList,
          'listMovies' => $listMovies,
          'listImages' => $listImages,
          'listUrls' => $listUrlsVideo,
          'listTags' => $listTags
            )
    );
  }

  public function getUrlsMovies($title, $imdbId) {
    $title = urlencode($title);
    $listUrlsVideo = array();
    $listUrlsVideo = $this->getContentFromCurl($title);
    return $listUrlsVideo;
  }

  public function getContentFromCurl($keyword) {

    $url = 'https://123movies.is/ajax/suggest_search';
    $fields = array(
      'keyword' => urlencode($keyword),
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
    $listMovie = 'https://123movies.is/ajax/v2_get_episodes/' . $urlMovie[0];

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
    foreach ($dom->find('.le-server') as $div) {
      $nameText = $div->find('.les-title strong', 0)->plaintext;
      $nameText = trim($nameText);

      $array = array('OpenLoad', 'VideoMega');

      if ($this->strposa($nameText, $array, 1)) {
        $i++;
        $hrefIframedata = $div->find('a', 0)->attr['episode-id'];
        $hrefIframequalite = $div->find('a', 0)->attr['title'];
        $hrefIframe = 'https://123movies.is/ajax/load_embed/' . $hrefIframedata;

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $hrefIframe);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Google');
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);

        $json_decoded = json_decode(stripslashes($query));
        if (!is_null($json_decoded)) {
          $parse = parse_url($json_decoded->embed_url);

          $listUrlsVideo[] = array(
            'url' => $json_decoded->embed_url,
            'name' => $parse['host'],
            'qualite' => $hrefIframequalite,
            'id' => $i,
            'type' => 'iframe',
            'host' => $parse['host']
          );
        }
      }
    }
    return $listUrlsVideo;
  }

  public function strposa($haystack, $needles = array(), $offset = 0) {
    $chr = array();
    foreach ($needles as $needle) {
      $res = strpos($haystack, $needle, $offset);
      if ($res !== false)
        $chr[$needle] = $res;
    }
    if (empty($chr))
      return false;
    return min($chr);
  }

  public function SaveUrlsMovies($listUrlMovies, $id, $save = TRUE) {

    $em = $this->getDoctrine()->getManager();

    if (!$save) {
      $repository = $this
          ->getDoctrine()
          ->getManager()
          ->getRepository('AppBundle:Item')
      ;
      $item = $repository->findOneBy(array('idApi' => $id));
    }else{
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
