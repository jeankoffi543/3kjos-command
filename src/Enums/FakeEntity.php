<?php

namespace Kjos\Command\Enums;

enum FakeEntity: string
{
   use Values;

   case USER = 'User';
   case PARTNER = 'Partner';
   case CLIENT = 'Client';

   public static function attributs($entity): array
   {
      return match ($entity) {
         self::USER => [
            'nom' ,
            'prenom' ,
            'email' ,
            'telephone' ,
            'adresse' ,
            'ville' ,
            'pays' ,
            'date_naissance' ,
            'dossier_id' ,
         ],

         self::PARTNER => [
            'nom_entreprise' ,
            'contact' ,
            'email' ,
            'telephone' ,
            'adresse' ,
            'ville' ,
            'pays' ,
            'rc',
            'compte_bancaire' ,
            'user_id' ,
            'police_id' ,
         ],

         self::CLIENT => [
            'numero',
            'type' ,
            'date_souscription' ,
            'date_expiration' ,
            'montant' ,
            'partner_id' ,
            'status' ,
         ],

         default => [],
      };
   }

   public static function type($attribut): array
   {
      return match ($attribut) {
         'status' => 'enum',
         'date_naissance', 'date_souscription', 'date_expiration' => 'date',
         'montant' => 'decimal',
         'compte_bancaire', 'rc', 'numero', 'nom', 'prenom', 'email', 'telephone', 'adresse', 'ville', 'pays', 'contact', 'nom_entreprise', 'type' => 'string',
         'user_id', 'partner_id', 'police_id', 'dossier_id' => 'unsignedBigInteger',
         default => [],
      };
   }
}
