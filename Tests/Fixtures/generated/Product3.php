/**
 * @ORM\Column(type="integer")
 * @Getter
 */
private $id;
/**
 * @var ?int
 * @Getter
 */
private $qty;
/**
 * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
 * @ORM\JoinColumn(nullable=true)
 * @Setter(fluent=true)
 */
private $prices;
/**
 * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="products")
 * @ORM\JoinColumn(nullable=false)
 * @Setter(fluent=true)
 */
private $images;
/**
 * Product3 constructor.
 *
 * @param int $id
 * @param ?int $qty
 * @param \Doctrine\Common\Collections\Collection|null $prices
 * @param \Doctrine\Common\Collections\Collection $images
 */
public function __construct(int $id, ?int $qty, ?\Doctrine\Common\Collections\Collection $prices = null, \Doctrine\Common\Collections\Collection $images)
{
    $this->id = $id;
    $this->qty = $qty;
    $this->prices = $prices;
    $this->images = $images;
}
/**
 * Product3 toString.
 *
 * @return string
 */
public function __toString() : string
{
    return (string) ('Product3: ' . ($this->id . (', ' . $this->qty)));
}
/**
 * Retrieves id
 *
 * @return int
 */
public function getId() : int
{
    return $this->id;
}
/**
 * Retrieves qty
 *
 * @return ?int
 */
public function getQty() : ?int
{
    return $this->qty;
}
/**
 * Sets prices
 *
 * @param \Doctrine\Common\Collections\Collection|null $prices
 * @return self
 */
public function setPrices(?\Doctrine\Common\Collections\Collection $prices) : self
{
    $prices->setProduct3($this);
    $this->prices = $prices;
    return $this;
}
/**
 * Adds price to prices
 *
 * @param \App\Entity\Price $price
 * @return self
 */
public function addPrice(\App\Entity\Price $price) : self
{
    if (!$this->prices->contains($price)) {
        $this->prices->add($price);
        $price->addProduct3($this);
    }
    return $this;
}
/**
 * Removes price from prices
 *
 * @param \App\Entity\Price $price
 * @return self
 */
public function removePrice(\App\Entity\Price $price) : self
{
    if ($this->prices->contains($price)) {
        $this->prices->removeElement($price);
        $price->removeProduct3($this);
    }
    return $this;
}
/**
 * Sets images
 *
 * @param \Doctrine\Common\Collections\Collection $images
 * @return self
 */
public function setImages(\Doctrine\Common\Collections\Collection $images) : self
{
    $images->setProduct3($this);
    $this->images = $images;
    return $this;
}
/**
 * Adds image to images
 *
 * @param \App\Entity\Image $image
 * @return self
 */
public function addImage(\App\Entity\Image $image) : self
{
    if (!$this->images->contains($image)) {
        $this->images->add($image);
        $image->setProduct3($this);
    }
    return $this;
}
/**
 * Removes image from images
 *
 * @param \App\Entity\Image $image
 * @return self
 */
public function removeImage(\App\Entity\Image $image) : self
{
    if ($this->images->contains($image)) {
        $this->images->removeElement($image);
        $image->setProduct3(null);
    }
    return $this;
}