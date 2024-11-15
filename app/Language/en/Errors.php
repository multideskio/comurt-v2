<?php

/**
 * CONFIG PT BR
 */
return [
    // Generic Error Messages
    'badRequest' => 'Invalid request.',
    'unauthorized' => 'Unauthorized. Invalid or missing token.',
    'forbidden' => 'You do not have permission to access this resource.',
    'notFound' => 'Resource not found.',
    'methodNotAllowed' => 'Method not allowed for this route.',
    'conflict' => 'Resource conflict. A resource with these details already exists.',
    'unsupportedMediaType' => 'Unsupported media type.',
    'unprocessableEntity' => 'Validation error. Some fields are invalid.',
    'tooManyRequests' => 'Too many requests. Please try again later.',
    'serverError' => 'Internal server error. Please try again later.',

    // Generic Success Messages
    'resourceCreated' => 'Resource successfully created.',
    'resourceUpdated' => 'Change made successfully.',
    'resourceDeleted' => 'Resource successfully deleted.',

    // Authentication-Related Messages
    'invalidCredentials' => 'Invalid credentials.',
    'accountDisabled' => 'Account disabled. Contact the administrator.',
    'tokenExpired' => 'Token expired. Please log in again.',
    'tokenInvalid' => 'Invalid or missing token.',

    // Validation Messages
    'validationError' => 'Validation error. Please check the submitted data.',
    'missingRequiredFields' => 'Required fields are missing.',
    'invalidData' => 'Invalid data provided.',
    'invalidFormat' => 'Invalid format provided.',

    'authorizationHeaderNotFound' => 'Authorization header not found',
    'logoutSuccessful' => 'Logout successful, token invalidated',
    'invalidToken' => 'Invalid token',

    'userNotAuthenticated' => 'User not authenticated or invalid user data.',
    'twoIdsRequired' => 'You need at least two IDs for comparison.',
    'anamnesesNotFound' => 'No anamneses found for the provided IDs.',

    'roleNotSpecified' => 'Role not specified.',

    'anamneseNotFound' => 'Anamnesis not found or you do not have permission to delete it.',
    'anamneseDeleteError' => 'Error deleting the anamnesis: {errors}.',

    'idIsRequired' => 'ID is required.'

];
