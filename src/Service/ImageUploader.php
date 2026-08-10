<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{

    /**
     * Initialise le service avec le dossier de destination.
     *
     * @param string $destination Chemin absolu du dossier où seront enregistrées les images.
     */
    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    protected string $destination;



    /**
     * Envoie une image sur le serveur.
     *
     * Un nom de fichier unique est généré afin d'éviter les conflits.
     * @param UploadedFile $file Fichier envoyé par l'utilisateur.
     * @return string Le nom du fichier enregistré.
     */

    public function upload(UploadedFile $file): string
    {
        $filename = uniqid(). '.' . $file->guessExtension();

        $file->move($this->destination, $filename);

        return $filename;
    }


    /**
     * Supprime une image du disque.
     *
     * Si le fichier n'existe pas, aucune erreur n'est générée.
     *
     * @param string $filename Nom du fichier à supprimer.
     *
     * @return void
     */

    public function delete(string $filename): void
    {
        $path = $this->destination . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
    }


    /**
     * Remplace une image existante.
     *
     * La nouvelle image est d'abord envoyée sur le serveur,
     * puis l'ancienne est supprimée.
     *
     * @param UploadedFile $file Nouvelle image.
     * @param string|null $oldFilename Ancien nom de fichier.
     * @return string Le nom de la nouvelle image.
     */
    public function replace(UploadedFile $file, ?string $oldFilename): string
    {
        $newFilename = $this->upload($file);

        if ($oldFilename) {
            $this->delete($oldFilename);
        }

        return $newFilename;
    }
}