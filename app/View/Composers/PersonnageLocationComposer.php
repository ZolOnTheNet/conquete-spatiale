<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Helpers\PersonnageLocation;

/**
 * View Composer pour injecter les informations de localisation du personnage
 */
class PersonnageLocationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $personnage = request()->attributes->get('personnage');

        if ($personnage) {
            $location = new PersonnageLocation($personnage);

            $view->with([
                'personnageLocation' => $location,
                'menuSections' => $location->getMenuSections(),
            ]);
        } else {
            $view->with([
                'personnageLocation' => null,
                'menuSections' => [],
            ]);
        }
    }
}
