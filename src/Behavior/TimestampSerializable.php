<?php
namespace App\Behavior;

use JMS\Serializer\Annotation as JMS;

/**
 * 
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 * 
 * @JMS\ExclusionPolicy("all")
 */
trait TimestampSerializable
{	
	/**
	 * @JMS\VirtualProperty()
	 * @JMS\SerializedName("createdAt")
	 * @JMS\Groups({"details", "summary"})
	 */
	public function serializeCreatedAt() :?\DateTime {
		return $this->createdAt;
	}
	
	/**
	 * @JMS\VirtualProperty()
	 * @JMS\SerializedName("updatedAt")
	 * @JMS\Groups({"details", "summary"})
	 */
	public function serializeUpdatedAt() :?\DateTime {
		return $this->updatedAt;
	}
}

