<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Urls
 *
 * @ORM\Table(name="urls")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemRepository")
 */
class Urls {

  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**

   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Item")
   * @ORM\JoinColumn(nullable=false)
   */
  private $item;

  /**
   * @var text
   *
   * @ORM\Column(name="url", type="text")
   */
  private $url;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=255)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="qualite", type="string", length=255)
   */
  private $qualite;

  /**
   * @var string
   *
   * @ORM\Column(name="type", type="string", length=255)
   */
  private $type;

  /**
   * @var string
   *
   * @ORM\Column(name="host", type="string", length=255)
   */
  private $host;

  /**
   * Get id
   *
   * @return integer
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return Urls
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set qualite
   *
   * @param string $qualite
   *
   * @return Urls
   */
  public function setQualite($qualite) {
    $this->qualite = $qualite;

    return $this;
  }

  /**
   * Get qualite
   *
   * @return string
   */
  public function getQualite() {
    return $this->qualite;
  }

  /**
   * Set item
   *
   * @param \AppBundle\Entity\Item $item
   *
   * @return Urls
   */
  public function setItem(\AppBundle\Entity\Item $item) {
    $this->item = $item;

    return $this;
  }

  /**
   * Get item
   *
   * @return \AppBundle\Entity\Item
   */
  public function getItem() {
    return $this->item;
  }

  /**
   * Set video
   *
   * @param string $video
   *
   * @return Urls
   */
  public function setVideo($video) {
    $this->video = $video;

    return $this;
  }

  /**
   * Get video
   *
   * @return string
   */
  public function getVideo() {
    return $this->video;
  }


    /**
     * Set url
     *
     * @param string $url
     *
     * @return Urls
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Urls
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return Urls
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}
