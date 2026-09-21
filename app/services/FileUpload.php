<?php

class FileUpload
{
    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    private array $allowedMimeTypes = [];

    private array $allowedExtensions = [];

    private int $maxFileSize = 10485760; // 10 MB

    private string $destination = '';

    private array $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        string $destination
    ) {
        $this->destination =
            rtrim($destination, '/');
    }


    /*
    |--------------------------------------------------------------------------
    | Configuration Methods
    |--------------------------------------------------------------------------
    */

    public function setAllowedMimeTypes(
        array $mimeTypes
    ): self {

        $this->allowedMimeTypes =
            $mimeTypes;

        return $this;
    }


    public function setAllowedExtensions(
        array $extensions
    ): self {

        $this->allowedExtensions =
            array_map(
                'strtolower',
                $extensions
            );

        return $this;
    }


    public function setMaxFileSize(
        int $bytes
    ): self {

        $this->maxFileSize =
            $bytes;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    */

    public function getErrors(): array
    {
        return $this->errors;
    }


    private function addError(
        string $message
    ): void {

        $this->errors[] = $message;
    }


    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
    array $file
): array|false
    {
        $this->errors = [];

        /*
        |--------------------------------------------------------------------------
        | Check upload
        |--------------------------------------------------------------------------
        */

        if (
            !isset($file['error']) ||
            is_array($file['error'])
        ) {
            $this->addError(
                'Invalid upload.'
            );

            return false;
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->addError(
                'No file selected.'
            );

            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError(
                'Upload failed.'
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | File size
        |--------------------------------------------------------------------------
        */

        if ($file['size'] > $this->maxFileSize) {

            $this->addError(
                'File exceeds the maximum size.'
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Extension
        |--------------------------------------------------------------------------
        */

        $extension =
            strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );

        if (
            !empty($this->allowedExtensions) &&
            !in_array(
                $extension,
                $this->allowedExtensions,
                true
            )
        ) {

            $this->addError(
                'Invalid file type.'
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | MIME type
        |--------------------------------------------------------------------------
        */

        $finfo =
            finfo_open(
                FILEINFO_MIME_TYPE
            );

        $mimeType =
            finfo_file(
                $finfo,
                $file['tmp_name']
            );

        finfo_close(
            $finfo
        );

        if (
            !empty($this->allowedMimeTypes) &&
            !in_array(
                $mimeType,
                $this->allowedMimeTypes,
                true
            )
        ) {

            $this->addError(
                'Invalid MIME type.'
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Destination folder
        |--------------------------------------------------------------------------
        */

        if (
            !is_dir(
                $this->destination
            )
        ) {

            mkdir(
                $this->destination,
                0755,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Secure filename
        |--------------------------------------------------------------------------
        */

        $filename =
            bin2hex(
                random_bytes(16)
            ) .
            '.' .
            $extension;

        $destination =
            $this->destination .
            '/' .
            $filename;

        /*
        |--------------------------------------------------------------------------
        | Move upload
        |--------------------------------------------------------------------------
        */

        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {

            $this->addError(
                'Unable to save the uploaded file.'
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return [

            'filename' =>
                $filename,

            'original_name' =>
                $file['name'],

            'extension' =>
                $extension,

            'mime_type' =>
                $mimeType,

            'size' =>
                (int) $file['size'],

            'path' =>
                $destination

        ];
    }

}