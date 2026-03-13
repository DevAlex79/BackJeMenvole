<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforme un modèle Product en réponse JSON normalisée.
 *
 * Rôle : découpler la représentation API du modèle Eloquent.
 *   - Renomme "title" en "name" pour le frontend Vue.js
 *   - Expose uniquement les champs utiles au client
 *   - Évite de surcharger le modèle avec de la logique de présentation
 *
 * Utilisation :
 *   return new ProductResource($product);
 *   return ProductResource::collection($products);
 */
class ProductResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau associatif.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id_product,
            // "name" est l'alias de "title" attendu par le frontend Vue.js
            'name'        => $this->title,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'image'       => $this->image,
            'alt'         => $this->alt,
            // Identifiants des relations pour que le front puisse filtrer
            'category_id' => $this->categories_id_category,
            'user_id'     => $this->users_id_user,
            // Relations chargées à la demande (null si non eager-loaded)
            'category'    => $this->whenLoaded('category'),
            'user'        => $this->whenLoaded('user'),
        ];
    }
}
