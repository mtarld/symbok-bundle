/**
 * @ORM\Column(type="integer", nullable=true)
 * @Getter()
 */
private $id;
/**
 * @var string
 * @Symbok\Nullable()
 * @Setter(fluent=true, nullable=false)
 */
private $name;
private $image;
public function __construct()
{
}
public function __toString()
{
    return $this->name;
}
public function getId()
{
    return $this->id;
}
public function setImage($image)
{
    $this->image = $image;
}
/**
 * Sets id
 *
 * @param int|null $id
 * @return self
 */
public function setId(?int $id) : self
{
    $this->id = $id;
    return $this;
}
/**
 * Retrieves name
 *
 * @return string|null
 */
public function getName() : ?string
{
    return $this->name;
}
/**
 * Sets name
 *
 * @param string $name
 * @return self
 */
public function setName(string $name) : self
{
    $this->name = $name;
    return $this;
}
/**
 * Retrieves image
 *
 * @return mixed|null
 */
public function getImage() : ?mixed
{
    return $this->image;
}