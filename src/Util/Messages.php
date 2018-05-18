<?php

namespace App\Util;

use App\Entity\Geocode;

class Messages
{
    private $notification;
    private $warning;
    private $error;

    public function getMessage(int $messageType = MessageType::Notification): ?string
    {
        switch ($messageType) {
            case MessageType::Notification:
                return $this->notification;
            case MessageType::Warning:
                return $this->warning;
            case MessageType::Error:
                return $this->error;
            default:
                throw new \InvalidArgumentException('Given message type is not defined.');
        }
    }

    private function setMessage(string $message, int $messageType = MessageType::Notification): self
    {
        switch ($messageType) {
            case MessageType::Notification:
                $this->notification = $message;
                return $this;
            case MessageType::Warning:
                $this->warning = $message;
                return $this;
            case MessageType::Error:
                $this->error = $message;
                return $this;
            default:
                throw new \InvalidArgumentException('Given message type is not defined.');
        }
    }

    public function setTravelMessage(Geocode $toLocation, float $distance, bool $returning = false): void
    {
        // 0 ID is reserved for home location
        if ($toLocation->getId() !== 0) {
            $id = $toLocation->getBrewery()->getId();
            $name = $toLocation->getBrewery()->getName();
        } else {
            $id = 'Home';
            $name = 'Some home name';
        }

        $lat = $toLocation->getLatitude();
        $lon = $toLocation->getLongitude();

        $this->setMessage(
            sprintf('%6s %-7s %-58s: %-15.10f, %-15.10f distance: %.2f km',
                    $returning ? '<-' : '->',
                    '[' . $id . ']',
                    $name,
                    $lat,
                    $lon,
                    $distance)
        );

    }

    public function setNoSolutionMessage(Geocode $geocode, float $distance, float $maxDistance): void
    {
        $this->setMessage(
            sprintf('The nearest brewery to home location is \'%s\', %.2f km away. '.
                    'Going there and back would require more distance than the currently '.
                    'allocated distance budget of %.2f km. No solution exists.',
                    $geocode->getBrewery()->getName(), $distance, $maxDistance),
            MessageType::Error
        );
    }

    public function setNoMoreLocationsMessage(): void
    {
        $this->setMessage('No more unvisited locations can be reached.', MessageType::Warning);
    }
}