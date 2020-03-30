<?php
namespace App\Entity;

use App\Behavior\TimestampSerializable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Oka\RESTRequestValidatorBundle\Serializer\Behavior\Contextable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="username", errorPath="username", message="user.username.already_used", groups={"Create"})
 * @UniqueEntity(fields="email", errorPath="email", message="user.email.already_used", groups={"Create", "Update"})
 * @JMS\ExclusionPolicy("all")
 */
class User implements AdvancedUserInterface, TimestampableInterface, UserInterface
{
	use TimestampableTrait, TimestampSerializable, Contextable;
	
	const ROLE_DEFAULT = 'ROLE_USER';
	const ROLE_ADMIN = 'ROLE_ADMIN';
	const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
	
	const PROPERTY_VERIFIED_EMAIL = 1;
	const PROPERTY_VERIFIED_PHONE_NUMBER= 2;
	
	/**
	 * @ORM\Id()
	 * @ORM\Column(type="uuid_binary_ordered_time")
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
	 * @JMS\Expose()
	 * @JMS\Type("string")
	 * @JMS\Groups({"summary", "details"})
	 * @var \Ramsey\Uuid\UuidInterface
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", unique=true)
	 * @JMS\Expose()
	 * @JMS\Groups({"summary", "details"})
	 * @var string
	 */
	protected $username;
	
	/**
	 * @ORM\Column(type="string", unique=true)
	 * @JMS\Expose()
	 * @JMS\Groups({"summary", "details"})
     * @Assert\Email
	 * @var string
	 */
	protected $email;
	
	/**
	 * @ORM\Column(name="phone_number", type="string", length=15, nullable=true)
	 * @JMS\Expose()
	 * @JMS\SerializedName("phoneNumber")
	 * @JMS\Groups({"summary", "details"})
	 * @var string
	 */
	protected $phoneNumber;
	
	/**
	 * @ORM\Column(type="string", length=148)
	 * @var string
	 */
	protected $password;
	
	/**
	 * @ORM\Column(type="array")
	 * @var array
	 */
	protected $roles;
	
	/**
	 * @ORM\Column(name="first_name", type="string", nullable=true)
	 * @JMS\Expose()
	 * @JMS\SerializedName("firstName")
	 * @JMS\Groups({"summary", "details"})
	 * @var string
	 */
	protected $firstName;
     
    /**
	 * @ORM\Column(name="last_name", type="string", nullable=true)
	 * @JMS\Expose()
	 * @JMS\SerializedName("lastName")
	 * @JMS\Groups({"summary", "details"})
	 * @var string
	 */
	protected $lastName;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @JMS\Expose()
	 * @JMS\Groups({"summary", "details"})
	 * @var bool
	 */
    protected $enabled;
    
    /**
	 * @ORM\Column(type="boolean")
	 * @JMS\Expose()
	 * @JMS\Groups({"summary", "details"})
	 * @var bool
	 */
	protected $locked;
	
	/**
	 * @ORM\Column(name="last_login", type="datetime", nullable=true)
	 * @JMS\Expose()
	 * @JMS\SerializedName("lastLogin")
	 * @JMS\Groups({"details"})
	 * @var \DateTime
	 */
	protected $lastLogin;
	
	/**
	 * @ORM\Column(name="account_expires_at", type="datetime", nullable=true)
	 * @JMS\Expose()
	 * @JMS\SerializedName("accountExpiresAt")
	 * @JMS\Groups({"details"})
	 * @var \DateTime
	 */
	protected $accountExpiresAt;
	
	/**
	 * @ORM\Column(name="credentials_expires_at", type="datetime", nullable=true)
	 * @JMS\Expose()
	 * @JMS\SerializedName("credentialsExpiresAt")
	 * @JMS\Groups({"details"})
	 * @var \DateTime
	 */
	protected $credentialsExpiresAt;

	/**
	 * @ORM\Column(name="confirmation_token", type="string", unique=true, nullable=true)
	 * @var string
	 */
	protected $confirmationToken;
	
	/**
	 * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $passwordRequestedAt;
	
	/**
	 * @var string The plain password
	 */
	private $plainPassword;
	
	public function __construct() {
		$this->roles[] = self::ROLE_DEFAULT;
        $this->enabled = false;
        $this->locked = false;
	}
	
	public function getId() :string
	{
		return (string) $this->id;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function setUsername(string $username) :self
	{
		$this->username = $username;
		return $this;
	}
	
	public function getEmail() :string
	{
		return $this->email;
	}
	
	public function setEmail(string $email) :self
	{
		$this->email = $email;
		return $this;
	}
	
	public function getPhoneNumber() :?string
	{
		return $this->phoneNumber;
	}
	
	public function setPhoneNumber(string $phoneNumber) :self
	{
		$this->phoneNumber = $phoneNumber;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function setPassword(string $password) :self
	{
		$this->password = $password;
		return $this;
	}
	
	public function hasRole(string $role) :bool
	{
		return in_array(strtoupper($role), $this->roles, true);
	}

	public function getRoles()
	{
		$roles = $this->roles;
		$roles[] = static::ROLE_DEFAULT;
		
		return array_unique($roles);
	}
	
	public function addRole(string $role) :self
	{
		$role = strtoupper($role);
		
		if (static::ROLE_DEFAULT !== $role) {
			if (false === in_array($role, $this->roles, true)) {
				$this->roles[] = $role;
			}
		}
		return $this;
	}
	
	public function setRoles(array $roles) :self
	{
		foreach ($roles as $role) {
			$this->addRole($role);
		}
		return $this;
	}
	
	public function removeRole(string $role) :self
	{
		$role = strtoupper($role);
		
		if (static::ROLE_DEFAULT !== $role) {
			if (false !== ($key = array_search($role, $this->roles, true))) {
				unset($this->roles[$key]);
				$this->roles = array_values($this->roles);
			}
		}
		return $this;
	}
	
	public function getFirstName() :?string
	{
		return $this->firstName;
	}
	
	public function setFirstName(string $firstName) :self
	{
		$this->firstName = $firstName;
		return $this;
	}
	
	public function getLastName() :?string
	{
		return $this->lastName;
	}
	
	public function setLastName(string $lastName) :self
	{
		$this->lastName = $lastName;
		return $this;
	}
	
	public function isEnabled() {
		return $this->enabled;
	}
	
	public function setEnabled(bool $enabled) :self
	{
		$this->enabled = $enabled;
		return $this;
    }
    
    public function isAccountNonLocked()
	{
		return !$this->locked;
	}
	
	public function setLocked(bool $locked) :self
	{
		$this->locked = $locked;
		return $this;
	}
	
	public function getLastLogin() :?\DateTime
	{
		return $this->lastLogin;
	}
	
	public function setLastLogin(\DateTime $lastLogin) :self
	{
		$this->lastLogin = $lastLogin;
		return $this;
	}
	
	public function getAccountExpiresAt() :?\DateTime
	{
		return $this->accountExpiresAt;
	}
	
	public function setAccountExpiresAt(\DateTime $expiresAt = null) :self
	{
		$this->accountExpiresAt = $expiresAt;
		return $this;
	}
	
	public function isAccountNonExpired()
	{
		return $this->accountExpiresAt instanceof \DateTime ? $this->accountExpiresAt->getTimestamp() >= time() : true;
	}
	
	public function getCredentialsExpiresAt() :?\DateTime
	{
		return $this->credentialsExpiresAt;
	}
	
	public function setCredentialsExpiresAt(\DateTime $expiresAt = null) :self 
	{
		$this->credentialsExpiresAt = $expiresAt;
		return $this;
	}
	
	public function isCredentialsNonExpired()
	{
		return $this->credentialsExpiresAt instanceof \DateTime ? $this->accountExpiresAt->getTimestamp() >= time() : true;
	}

	public function getConfirmationToken() :?string
	{
		return $this->confirmationToken;
	}
	
	public function setConfirmationToken(string $confirmationToken = null) :self
	{
		$this->confirmationToken = $confirmationToken;
		return $this;
	}
	
	public function getPasswordRequestedAt() :?\DateTime
	{
		return $this->passwordRequestedAt;
	}
	
	public function setPasswordRequestedAt(\DateTime $passwordRequestedAt = null) :self
	{
		$this->passwordRequestedAt = $passwordRequestedAt;
		return $this;
	}
	
	public function isPasswordRequestNonExpired(int $ttl) :bool
	{
		return $this->passwordRequestedAt instanceof \DateTime && $this->passwordRequestedAt->getTimestamp() + $ttl > time();
	}
	
	public function getSalt() {}
	
	public function getPlainPassword() :string
	{
		return $this->plainPassword;
	}
	
	public function setPlainPassword(string $plainPassword) :self
	{
		$this->plainPassword = $plainPassword;
		return $this;
	}
	
	public function eraseCredentials()
	{
		$this->plainPassword = null;
    }

    /**
	 * @Assert\Callback(groups={"Create", "Update"})
	 */
	public function validateUsername(ExecutionContextInterface $context)
	{
		switch (1) {
			case preg_match('#^(\+)?[0-9]+$#', $this->username):
				$builder = $context->buildViolation('user.username.invalid.composition');
				break;
				
			case preg_match('#[/[:blank:][:space:]@]#', $this->username):
				$builder = $context->buildViolation('user.username.invalid.characters');
				break;
		}
		
		if (true === isset($builder)) {
			$builder->atPath('username')
					->setInvalidValue($this->username)
					->addViolation();
		}
	}
	
	/**
	 * @Assert\Callback(groups={"Create", "ChangePassword"})
	 */
	public function validatePassword(ExecutionContextInterface $context)
	{
		$length = strlen($this->plainPassword);
		
		switch (true) {
			case null === $this->plainPassword:
				break;
				
			case $length < 8:
				$builder = $context->buildViolation('user.password.short');
				break;
				
			case $length > 4096:
				$builder = $context->buildViolation('user.password.long');
				break;
				
			case strtolower($this->username) === strtolower($this->plainPassword):
				$builder = $context->buildViolation('user.password.equal_username');
				break;
				
			case strtolower($this->email) === strtolower($this->plainPassword):
				$builder = $context->buildViolation('user.password.equal_email');
				break;
				
			case (bool) preg_match(sprintf('#%s#', $this->plainPassword), $this->phoneNumber):
				$builder = $context->buildViolation('user.password.equal_phone_number');
				break;
		}
		
		if (true === isset($builder)) {
			$builder->atPath('[password]')
					->setInvalidValue($this->plainPassword)
					->addViolation();
		}
	}

}
