<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Enums\ColumnIndex;
use Kjos\Command\Enums\ColumnModifier;
use Kjos\Command\Enums\ColumnType;
use Kjos\Command\Factories\BuilderFactory;

class RequestKitProvider
{
   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function authorize(): string
   {
      return <<<REQUEST
            public function authorize()
            {
               return true;
            }
         REQUEST;
   }

   public function failedValidation(): string
   {
      return <<<REQUEST
         /**
         * @return mixed
         */
         public function failedValidation(Validator \$validator)
         {
            if (boolval(request()->headers->get("x-inertia")) === false) {
               throw new HttpResponseException(
                  response()->json([
                    'errors' => \$validator->errors(),
                  ], \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST)
               );
            }
         }
         REQUEST;
   }

   public function rules(): string
   {
      $rules = [];
      $attributes = $this->factory->entity->getAttributes();

      /** @var \Kjos\Command\Managers\Attribut $attribute */
      foreach ($attributes as $attribute) {
         $allRules = [];

         // Règles à partir du type
         $typeRules = ColumnType::rules($attribute->getColumnType()->toArray());
         $allRules[] = $typeRules;

         // Règles à partir des modificateurs
         foreach ($attribute->getModifiers() as $modifier) {
            $allRules[] = ColumnModifier::rules($modifier->toArray());
         }

         // Règles à partir des indexes
         foreach ($attribute->getIndexes() as $index) {
            $allRules[] = ColumnIndex::rules($this->factory->entity->getName(), $attribute, $index->toArray());
         }

         // Exploser, nettoyer, filtrer les doublons
         $flattenedRules = [];
         foreach ($allRules as $ruleString) {
            if (!empty($ruleString)) {
               foreach (explode(',', $ruleString) as $r) {
                  $flattenedRules[] = trim($r);
               }
            }
         }

         $uniqueRules = array_unique(array_filter($flattenedRules));
         $compiledRules = implode(',', $uniqueRules);

         $rules[] = "'{$attribute->getName()}' => [{$compiledRules}]";
      }

      $rules = implode(',' . PHP_EOL, $rules);

      return <<<REQUEST
            /**
          * @return string[]
          */
            public function rules(): array
            {
               if (\$this->isMethod(FormRequest::METHOD_GET)) {
                     return [];
               }

               \$rules = [
                  {$rules}
               ];

               if (\$this->isMethod(FormRequest::METHOD_PUT)) {
                     \$rules = array_merge(\$rules, []);
               }

               return \$rules;
            }
         REQUEST;
   }

   public function prepareForValidation(): string
   {
      return <<<REQUEST
           public function prepareForValidation()
            {
               // Forcer l'image à être un UploadedFile si c’est un FormData
               if (\$this->hasFile('image')) {
                     \$this->merge([
                        'image' => \$this->file('image'),
                     ]);
               }
            }
         REQUEST;
   }
}
