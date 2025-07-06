<?php

namespace Kjos\Command\Managers;

class ColumnType
{
   protected ?string $type;
   protected ?int $length;
   protected ?bool $fixed = false;
   protected ?string $charset;
   protected ?int $total;
   protected ?int $places;
   protected ?int $precision;
   protected ?array $enum;
   protected ?string $subtype;
   protected ?int $srid;
   protected ?int $dimensions;

   public function setType(?string $type): static
   {
      $this->type = $type;
      return $this;
   }
   public function getType(): ?string
   {
      return $this->type;
   }


   public function setLength(?int $length): static
   {
      $this->length = $length;
      return $this;
   }
   public function getLength(): ?int
   {
      return $this->length;
   }

   public function setFixed(?bool $fixed): static
   {
      $this->fixed = $fixed;
      return $this;
   }
   public function getFixed(): ?bool
   {
      return $this->fixed;
   }

   public function setCharset(?string $charset): static
   {
      $this->charset = $charset;
      return $this;
   }
   public function getCharset(): ?string
   {
      return $this->charset;
   }

   public function setTotal(?int $total): static
   {
      $this->total = $total;
      return $this;
   }
   public function getTotal(): ?int
   {
      return $this->total;
   }

   public function setPlaces(?int $places): static
   {
      $this->places = $places;
      return $this;
   }
   public function getPlaces(): ?int
   {
      return $this->places;
   }

   public function setPrecision(?int $precision): static
   {
      $this->precision = $precision;
      return $this;
   }
   public function getPrecision(): ?int
   {
      return $this->precision;
   }

   public function setEnum(?array $enum): static
   {
      $this->enum = $enum;
      return $this;
   }
   public function getEnum(): ?array
   {
      return $this->enum;
   }

   public function setSubtype(?string $subtype): static
   {
      $this->subtype = $subtype;
      return $this;
   }
   public function getSubtype(): ?string
   {
      return $this->subtype;
   }

   public function setSrid(?int $srid): static
   {
      $this->srid = $srid;
      return $this;
   }
   public function getSrid(): ?int
   {
      return $this->srid;
   }

   public function setDimensions(?int $dimensions): static
   {
      $this->dimensions = $dimensions;
      return $this;
   }
   public function getDimensions(): ?int
   {
      return $this->dimensions;
   }

   public function toArray(): array
   {
      return [
         'type' => $this->type,
         'length' => $this->length,
         'fixed' => $this->fixed,
         'charset' => $this->charset,
         'total' => $this->total,
         'places' => $this->places,
         'precision' => $this->precision,
         'enum' => $this->enum,
         'subtype' => $this->subtype,
         'srid' => $this->srid,
         'dimensions' => $this->dimensions,
      ];
   }
}
