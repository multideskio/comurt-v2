<?php

namespace App\Controllers\Api\V2;

use App\Controllers\BaseController;
use App\Libraries\BucketLibrary;
use App\Models\CustomerUser\V2\CreateCustomerUser;
use App\Models\MagicLink\V2\GetEmailMagicLink;
use CodeIgniter\API\ResponseTrait;
use App\Models\CustomerUser\V2\GetCustomerUser;
//TODO: quando criar um usuario verifcar se usuario ja existe atraves do magic link dele
class CustomerUser extends BaseController
{
    use ResponseTrait;

    public function create($hash): \CodeIgniter\HTTP\ResponseInterface
    {
        $magicLinkModel = new GetEmailMagicLink();
        $email = $magicLinkModel->getEmailMagicLink($hash);

        if ($email == '') {
            return $this->fail('Not Allow', 403);
        }

        $data = $this->request->getPost();
        $files = $this->request->getFiles();

        $requiredFields = [
            'nome_de_exibicao', 'localizacao', 'telefone', 'area_de_atuacao', 'abordagens',
            'formacao', 'experiencia', 'biografia'
        ];

        $requiredFiles = ['foto_perfil', 'foto_documento', 'foto_cnh'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->fail("The field {$field} is required and cannot be null", 400);
            }
        }

        foreach ($requiredFiles as $fileField) {
            if (empty($files[$fileField]) || !$files[$fileField]->isValid()) {
                return $this->fail("The file {$fileField} is required and must be a valid file", 400);
            }
        }

        $allowedFileTypes = ['image/png', 'image/jpg', 'image/jpeg'];

        $bucketLibrary = new BucketLibrary();
        $bucketName = 'marimarketingplace';
        $savedFiles = [];

        foreach ($files as $field => $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                if (in_array($file->getMimeType(), $allowedFileTypes)) {
                    $fileExtension = $file->getClientExtension();
                    $friendlyName = $field . '_' . $email . '_' . time() . '.' . $fileExtension;
                    $filePath = $file->getTempName();
                    $result = $bucketLibrary->saveFile($bucketName, $filePath, $friendlyName);
                    if ($result) {
                        $data[$field] = $friendlyName;
                        $savedFiles[$field] = [$friendlyName, $fileExtension, base64_encode(file_get_contents($filePath))];
                    } else {
                        return $this->fail('Failed to upload ' . $field, 500);
                    }
                } else {
                    return $this->fail('Invalid file type for ' . $field, 400);
                }
            }
        }

        $customerUserModel = new CreateCustomerUser();
        $result = $customerUserModel->createCustomerUser($data, $email, $savedFiles);

        if ($result) {
            return $this->respond(['message' => 'Customer user created successfully'], 201);
        } else {
            return $this->fail('Failed to create customer user', 500);
        }
    }

    public function get($hash): \CodeIgniter\HTTP\ResponseInterface
    {
        $magicLinkModel = new GetEmailMagicLink();
        $email = $magicLinkModel->getEmailMagicLink($hash);

        if ($email == '') {
            return $this->fail('Not Allow', 403);
        }

        $customerUserModel = new GetCustomerUser();
        $customerUser = $customerUserModel->getUserByEmail($email);

        if ($customerUser) {
            return $this->respond($customerUser);
        } else {
            return $this->fail('Customer user not found', 404);
        }

    }
}