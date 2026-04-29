<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Helper pour la gestion du temps in-game
 * Date de référence : 1er janvier 3000 à 00:00:00
 */
class GameTimeHelper
{
    /**
     * Date de référence du jeu (début de la conquête spatiale)
     */
    const DATE_REFERENCE = '3000-01-01 00:00:00';

    /**
     * Timestamp de référence en jours (0 = 1er janvier 3000)
     */
    const TIMESTAMP_REFERENCE_JOURS = 0;

    /**
     * Convertir une date Carbon en nombre de jours depuis la référence
     *
     * @param Carbon $date Date in-game
     * @return float Nombre de jours depuis 3000-01-01 (peut être décimal)
     */
    public static function dateToJours(Carbon $date): float
    {
        $reference = Carbon::parse(self::DATE_REFERENCE);
        // Inverser l'ordre : reference->diff(date) au lieu de date->diff(reference)
        // pour obtenir un nombre positif quand date > reference
        return $reference->floatDiffInDays($date, false); // false = peut être négatif
    }

    /**
     * Convertir un nombre de jours depuis la référence en date Carbon
     *
     * @param float $jours Nombre de jours depuis 3000-01-01
     * @return Carbon Date in-game
     */
    public static function joursToDate(float $jours): Carbon
    {
        $reference = Carbon::parse(self::DATE_REFERENCE);
        return $reference->copy()->addDays($jours);
    }

    /**
     * Obtenir la date actuelle du jeu pour un personnage
     *
     * @param \App\Models\Personnage $personnage
     * @return Carbon Date in-game actuelle
     */
    public static function getDateActuelleJeu($personnage): Carbon
    {
        // Logique selon SYSTEME_TEMPOREL.md
        // TODO: Implémenter le calcul basé sur PA et dernière connexion
        // Pour l'instant, retourner derniere_connexion ou date de référence

        if ($personnage->derniere_connexion) {
            return Carbon::parse($personnage->derniere_connexion);
        }

        return Carbon::parse(self::DATE_REFERENCE);
    }

    /**
     * Obtenir le timestamp en jours pour la date actuelle du jeu
     *
     * @param \App\Models\Personnage $personnage
     * @return float Nombre de jours depuis 3000-01-01
     */
    public static function getTimestampJoursActuel($personnage): float
    {
        $dateActuelle = self::getDateActuelleJeu($personnage);
        return self::dateToJours($dateActuelle);
    }

    /**
     * Formater une date in-game pour affichage
     *
     * @param Carbon $date
     * @param string $format Format (défaut: 'd M Y H:i')
     * @return string Date formatée
     */
    public static function formatDateJeu(Carbon $date, string $format = 'd M Y H:i'): string
    {
        return $date->format($format);
    }

    /**
     * Calculer la différence en jours entre deux dates in-game
     *
     * @param Carbon $date1
     * @param Carbon $date2
     * @return float Nombre de jours (peut être décimal)
     */
    public static function differenceEnJours(Carbon $date1, Carbon $date2): float
    {
        return $date1->floatDiffInDays($date2, false);
    }
}
