<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// Client id from db - Global Variable
$client_id = NULL;
$proff_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $client_id;
            // get user primary key id
            $client_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticated(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidProffApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $proff_id;
            // get user primary key id
            $proff_id = $db->getProffId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
/**
 * Client Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('first_name','last_name', 'email', 'password'));

            $response = array();

            // reading post params
            $client_name = $app->request->post('client_name');
            $first_name = $app->request->post('first_name');
            $last_name = $app->request->post('last_name');
            $email = $app->request->post('email');
            $password = $app->request->post('password');
            $cell_no = $app->request->post('cell_no');
            $location = $app->request->post('location');
            $image = $app->request->post('image');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createClient($first_name, $last_name, $email, $password);

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while registering";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this email already existed";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * Client Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getClientByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response["client_id"] = $user["client_id"];
                    $response["client_name"] = $user["client_name"];
                    $response["first_name"] = $user["first_name"];
                    $response["last_name"] = $user["last_name"];
                    $response["cell_no"] = $user["cell_no"];
                    $response["location"] = $user["location"];
                    $response["image"] = $user["image"];
                    $response["email"] = $user["email"];
                    $response["status"] = $user["status"];
                    $response['api_key'] = $user['api_key'];
                    $response["createdAt"] = $user["created_at"];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        });

/**
 * Listing all clients
 * method GET
 * url /clients
 */
$app->get('/clients', 'authenticate', function() {
            global $client_id;
            $response = array();
            $db = new DbHandler();

            // fetching all clients
            $result = $db->getAllClients();
            if ($result != NULL) {
                $response["error"] = false;
                $response['message'] = "Clients exist";
                $response["clients"] = array();

                // looping through result and preparing clients array
                while ($client = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp["client_id"] = $client["client_id"];
                    $tmp["client_name"] = $client["client_name"];
                    $tmp["first_name"] = $client["first_name"];
                    $tmp["last_name"] = $client["last_name"];
                    $tmp["cell_no"] = $client["cell_no"];
                    $tmp["location"] = $client["location"];
                    $tmp["image"] = $client["image"];
                    $tmp["email"] = $client["email"];
                    $tmp["status"] = $client["status"];
                    $tmp["createdAt"] = $client["created_at"];
                    array_push($response["clients"], $tmp);
                }

                echoRespnse(200, $response);
            }
            else{
                $response['error'] = true;
                $response['message'] = "Clients do not exist";
                echoRespnse(404, $response);
            }
        });


/**
 * Listing single client
 * method GET
 * url /clients/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/client/:id', 'authenticate', function($client_id) {
//            global $client_id;
            $response = array();
            $db = new DbHandler();

            // fetch single client
            $result = $db->getClient($client_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response["client_id"] = $result["client_id"];
                $response["client_name"] = $result["client_name"];
                $response["first_name"] = $result["first_name"];
                $response["last_name"] = $result["last_name"];
                $response["cell_no"] = $result["cell_no"];
                $response["location"] = $result["location"];
                $response["image"] = $result["image"];
                $response["email"] = $result["email"];
                $response["status"] = $result["status"];
                $response['api_key'] = $result['api_key'];
                $response["createdAt"] = $result["created_at"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });

/**
 * Deleting an existing client
 * method DELETE
 * url - /clients/:id
 */

$app->delete('/client/:id', 'authenticate', function($client_id) use($app) {

    $db = new DbHandler();
    $response = array();
    $result = $db->deleteClient($client_id);
    if ($result) {
        // client deleted successfully
        $response["error"] = false;
        $response["message"] = "Account deleted succesfully";
    } else {
        // client failed to delete
        $response["error"] = true;
        $response["message"] = "Account failed to delete. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 * Proffessional end points
 */
/**
 * professional Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/professional_register', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('first_name', 'last_name', 'email', 'password'));

    $response = array();

    // reading post params
    $first_name = $app->request->post('first_name');
    $last_name = $app->request->post('last_name');
    $email = $app->request->post('email');
    $password = $app->request->post('password');

    // validating email address
    validateEmail($email);

    $db = new DbHandler();
    $res = $db->createProfessional($first_name, $last_name, $email, $password);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registering";
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Sorry, this email already existed";
    }
    // echo json response
    echoRespnse(201, $response);
});

/**
 * professional Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/professional_login', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('email', 'password'));

    // reading post params
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $response = array();

    $db = new DbHandler();
    // check for correct email and password
    if ($db->checkProffLogin($email, $password)) {
        // get the user by email
        $professional = $db->getUserByEmail($email);
        if ($professional != NULL) {

            $proff_id = $professional['proff_id'];
            $response['error'] = false;
            $response['message'] = 'Login successful';
            $response['professional'] = $db->getUserByEmail($email);

            $db->createProfessionalStatus($proff_id);
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
    }
    else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoRespnse(200, $response);
});

/**
 * ------------------------ METHODS WITH AUTHENTICATION ------------------------
 */

/**
 * Listing all professionals
 * method GET
 * url /professionals
 */
$app->get('/professionals', 'authenticated', function() {

    $response = array();
    $db = new DbHandler();

    // fetching all professionals
    $result = $db->getAllprofessionals();
    if ($result != NULL)
    {
        $response["error"] = false;
        $response["message"] = "Professionals exist.";
        $response["professionals"] = array();

        // looping through result and preparing professionals array
        while ($professional = $result->fetch_assoc()) {
            $tmp = array();
            $tmp['proff_id'] = $professional['proff_id'];
            $tmp['proff_name'] = $professional['proff_name'];
            $tmp['email'] = $professional['email'];
            $tmp['cell_no'] = $professional['cell_no'];
            $tmp['national_id'] = $professional['national_id'];
            $tmp['location'] = $professional['location'];
            $tmp['availability_status'] = $professional['availability_status'];
            $tmp['image'] = $professional['image'];
            $tmp['first_name'] = $professional['first_name'];
            $tmp['last_name'] = $professional['last_name'];
            $tmp['api_key'] = $professional['api_key'];
            $tmp['gender'] = $professional['gender'];
            $tmp['status'] = $professional['status'];
            $tmp['created_at'] = $professional['created_at'];
            array_push($response["professionals"], $tmp);
        }
        echoRespnse(200, $response);
    }
    else
    {
        $response["error"] = true;
        $response["message"] = "Professionals do not exist.";
        echoRespnse(404, $response);
    }

});

/**
 * Listing single professional
 * method GET
 * url /professional/:id
 * Will return 404 if the professional doesn't exist
 */
$app->get('/professionals/:id', 'authenticated', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch professional
    $result = $db->getProfessional($proff_id);
    if ($result != NULL) {
        $response["error"] = false;
        $response["message"] = "Professional exist.";
        $response["professional"] = array();
        $tmp = array();

        $tmp['proff_id'] = $result['proff_id'];
        $tmp['proff_name'] = $result['proff_name'];
        $tmp['email'] = $result['email'];
        $tmp['cell_no'] = $result['cell_no'];
        $tmp['national_id'] = $result['national_id'];
        $tmp['location'] = $result['location'];
        $tmp['availability_status'] = $result['availability_status'];
        $tmp['image'] = $result['image'];
        $tmp['first_name'] = $result['first_name'];
        $tmp['last_name'] = $result['last_name'];
        $tmp['api_key'] = $result['api_key'];
        $tmp['gender'] = $result['gender'];
        $tmp['status'] = $result['status'];
        $tmp['created_at'] = $result['created_at'];

        array_push($response["professional"], $tmp);
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Professional doesn't exist";
        echoRespnse(404, $response);
    }
});

/**
 * Updating existing professional
 * method PUT
 * params proff_name, cell_no, national_id, location, image, first_name, last_name, gender
 * url - /professionals/:id
 */
$app->put('/professionals/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('proff_name', 'cell_no', 'national_id', 'location', 'image', 'first_name', 'last_name', 'gender'));

    $proff_name = $app->request->put('proff_name');
    $cell_no = $app->request->put('cell_no');
    $national_id = $app->request->put('national_id');
    $location = $app->request->put('location');
    $image = $app->request->put('image');
    $first_name = $app->request->put('first_name');
    $last_name = $app->request->put('last_name');
    $gender = $app->request->put('gender');

    $db = new DbHandler();
    $response = array();

    // updating professional details
    $result = $db->updateProfessional($proff_name, $cell_no, $national_id, $location, $image, $first_name, $last_name, $gender, $proff_id);
    if ($result) {
        // personal details updated successfully
        $response["error"] = false;
        $response["message"] = "Personal details updated successfully";
    } else {
        // personal details failed to update
        $response["error"] = true;
        $response["message"] = "Personal details failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Updating existing professional
 * method PUT
 * params password
 * url - /change_professionals_password/:id
 */
$app->put('/change_professionals_password/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('password'));

    $response = array();

    // reading post params
    $password = $app->request->post('password');

    $db = new DbHandler();
    $res = $db->changeProfessionalPassword($password, $proff_id);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Password changed successfully";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while changing password";
    }

    // echo json response
    echoRespnse(201, $response);
});

/**
 * Updating existing professional availability
 * method PUT
 * params availability_status
 * url - /change_professionals_availability/:id
 */
$app->put('/change_professionals_availability/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('availability_status'));

    $response = array();

    // reading post params
    $availability_status = $app->request->post('availability_status');

    $db = new DbHandler();
    $res = $db->changeprofessionalAvailability($availability_status, $proff_id);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Congratulation, You won the bid";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while confirming your bid";
    }

    // echo json response
    echoRespnse(201, $response);
});

/**
 * Deactivate existing professional
 * method PUT
 * params status
 * url - /deactivate_professionals/:id
 */
$app->put('/deactivate_professionals/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('status'));

    $status = $app->request->put('status');

    $db = new DbHandler();
    $response = array();

    // updating professional details
    $result = $db->deactivate_activateProfessional($status, $proff_id);
    if ($result) {
        // personal details updated successfully
        $response["error"] = false;
        $response["message"] = "Account deleted successfully";
    } else {
        // personal details failed to update
        $response["error"] = true;
        $response["message"] = "Account delete failed. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Activate existing professional
 * method PUT
 * params status
 * url - /activate_professionals/:id
 */
$app->put('/activate_professionals/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('status'));

    $status = $app->request->put('status');

    $db = new DbHandler();
    $response = array();

    // updating professional details
    $result = $db->deactivate_activateProfessional($status, $proff_id);
    if ($result) {
        // personal details updated successfully
        $response["error"] = false;
        $response["message"] = "Account activated successfully";
    } else {
        // personal details failed to update
        $response["error"] = true;
        $response["message"] = "Account activation failed. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Updating existing professional status
 * method PUT
 * params proff_text
 * url - /profile_text/:id
 */
$app->put('/profile_text/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('proff_text'));

    $response = array();

    // reading post params
    $proff_text = $app->request->post('proff_text');

    $db = new DbHandler();
    $res = $db->changeProfessionalTextStatus($proff_text, $proff_id);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Profile status updated successfully";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while updating profile status";
    }

    // echo json response
    echoRespnse(201, $response);
});

/**
 * Updating existing professional status
 * method PUT
 * params proff_image
 * url - /profile_image/:id
 */
$app->put('/profile_image/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('proff_image'));

    $response = array();

    // reading post params
    $proff_image = $app->request->post('proff_image');

    $db = new DbHandler();
    $res = $db->changeProfessionalImageStatus($proff_image, $proff_id);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Profile status updated successfully";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while updating profile status";
    }

    // echo json response
    echoRespnse(201, $response);
});

/**
 * Updating existing professional status
 * method PUT
 * params proff_video
 * url - /proff_video/:id
 */
$app->put('/profile_video/:id', 'authenticated', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('proff_video'));

    $response = array();

    // reading post params
    $proff_video = $app->request->post('proff_video');

    $db = new DbHandler();
    $res = $db->changeProfessionalVideoStatus($proff_video, $proff_id);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Profile status updated successfully";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while updating profile status";
    }

    // echo json response
    echoRespnse(201, $response);
});

/**
 * Deleting professional. professional can delete only their profile
 * method DELETE
 * url /professionals
 */
$app->delete('/professionals/:id', 'authenticated', function($proff_id) use($app) {

    $db = new DbHandler();
    $response = array();
    $result = $db->deleteProfessional($proff_id);
    if ($result) {
        // professional deleted successfully
        $response["error"] = false;
        $response["message"] = "Professional account deleted succesfully";
    } else {
        // professional failed to delete
        $response["error"] = true;
        $response["message"] = "Professional account failed to delete. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * professional rating. clients can rate the professional after work
 * method POST
 * url /professional_rating/:id
 */
$app->post('/professional_rating/:id', 'authenticated', function($proff_id) use($app) {

    $client_id = $app->request->put('client_id');
    $rating = $app->request->put('rating');

    $db = new DbHandler();
    $response = array();
    $result = $db->rateProfessional($client_id, $proff_id, $rating);
    if ($result) {
        // professional deleted successfully
        $response["error"] = false;
        $response["message"] = "Professional rating succesfully";
    } else {
        // professional failed to delete
        $response["error"] = true;
        $response["message"] = "Professional rating failed. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Listing single professional ratings
 * method GET
 * url /professional_status/:id
 * Will return 404 if the professional rating doesn't exist
 */
$app->get('/professional_rating/:id', 'authenticated', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch professional
    $result = $db->getProfessionalRating($proff_id);

    if ($result != NULL) {
        $response["error"] = false;
        $response['client_id'] = $result['client_id'];
        $response['proff_id'] = $result['proff_id'];
        $response['rating'] = $result['rating'];
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

/**
 * Listing single professional status
 * method GET
 * url /professional_status/:id
 * Will return 404 if the professional status doesn't exist
 */
$app->get('/professional_status/:id', 'authenticated', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch professional
    $result = $db->getProfessionalStatus($proff_id);

    if ($result != NULL) {
        $response["error"] = false;
        $response['proff_id'] = $result['proff_id'];
        $response['proff_text'] = $result['proff_text'];
        $response['proff_image'] = $result['proff_image'];
        $response['proff_video'] = $result['proff_video'];
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

// End of the professional endpoints




















































/**
 * Updating an existing client
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/client/:id', 'authenticate', function($client_id) use($app) {
    // check for required params
    verifyRequiredParams(array('client_name', 'first_name', 'last_name', 'cell_no', 'location', 'image', 'status'));

    //    global $client_id;
    $client_name = $app->request->put('client_name');
    $first_name = $app->request->put('first_name');
    $last_name = $app->request->put('last_name');
    $cell_no = $app->request->put('cell_no');
    $location = $app->request->put('location');
    $image = $app->request->put('image');
    $status = $app->request->put('status');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->updateClient( $client_name, $first_name, $last_name, $cell_no, $location, $image, $status, $client_id);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Your profile updated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Your profile failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});


/**
 * Deactivate an existing client
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/client_deactivate/:id', 'authenticate', function($client_id) use($app) {
    // check for required params
    verifyRequiredParams(array('status'));

    //    global $client_id;
    $status = $app->request->put('status');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->deactivateClient( $status, $client_id);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Your account deactivated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Your account failed to deactivate. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Client Job Post
 * url - /create_job_post
 * method - POST
 * params - name, email, password
 */
$app->post('/create_job_post', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('location','apartment_name', 'house_no', 'contact_cell_no', 'job_date', 'job_time', 'job_category'));
    $response = array();

    // reading post params
    $location = $app->request->post('location');
    $apartment_name = $app->request->post('apartment_name');
    $house_no = $app->request->post('house_no');
    $contact_cell_no = $app->request->post('contact_cell_no');
    $job_date = $app->request->post('job_date');
    $job_time = $app->request->post('job_time');
    $job_category = $app->request->post('job_category');

    $db = new DbHandler();
    $res = $db->createJob_post($location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "job successfully posted";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while posting";
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Sorry, job post already made";
    }
    // echo json response
    echoRespnse(201, $response);
});

/**
 * Updating a job post using client_id
 * method PUT
 * params task, status
 * url - /job_posts/:id
 */
$app->put('/job_posts_client/:id', 'authenticate', function($client_id) use($app) {
    // check for required params
    verifyRequiredParams(array('apartment_name', 'house_no', 'contact_cell_no', 'job_date', 'location', 'job_time', 'job_category'));

    //    global $client_id;
    $apartment_name = $app->request->put('apartment_name');
    $house_no = $app->request->put('house_no');
    $contact_cell_no = $app->request->put('contact_cell_no');
    $job_date = $app->request->put('job_date');
    $location = $app->request->put('location');
    $job_time = $app->request->put('job_time');
    $proff_id = $app->request->put('proff_id');
    $job_post_id = $app->request->put('job_post_id');
    $job_category = $app->request->put('job_category');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->updateJob_PostClient($location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category, $proff_id, $client_id, $job_post_id);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Task updated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Task failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Updating a job post using proff_id
 * method PUT
 * params task, status
 * url - /job_posts/:id
 */
$app->put('/job_posts_proff/:id', 'authenticate', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('apartment_name', 'house_no', 'contact_cell_no', 'job_date', 'location', 'job_time', 'job_category'));

    //    global $client_id;
    $apartment_name = $app->request->put('apartment_name');
    $house_no = $app->request->put('house_no');
    $contact_cell_no = $app->request->put('contact_cell_no');
    $job_date = $app->request->put('job_date');
    $location = $app->request->put('location');
    $job_time = $app->request->put('job_time');
    $client_id = $app->request->put('client_id');
    $job_post_id = $app->request->put('job_post_id');
    $job_category = $app->request->put('job_category');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->updateJob_PostProff($location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category, $proff_id,  $job_post_id);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Job updated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Job failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Deleting an existing job_post using client_id
 * method DELETE
 * url - /job_post_client/:id
 */

$app->delete('/job_post_client/:id', 'authenticate', function($client_id) use($app) {

    $db = new DbHandler();
    $response = array();
    $result = $db->delete_job_post_Client($client_id);
    if ($result) {
        // client deleted successfully
        $response["error"] = false;
        $response["message"] = "Task deleted succesfully";
    } else {
        // client failed to delete
        $response["error"] = true;
        $response["message"] = "Task failed to delete. Please try again!";
    }
    echoRespnse(200, $response);
});


/**
 * Deleting an existing job_post using Proff_id
 * method DELETE
 * url - /clients/:id
 */

$app->delete('/job_post_proff/:id', 'authenticate', function($proff_id) use($app) {

    $db = new DbHandler();
    $response = array();
    $result = $db->delete_job_post_proff($proff_id);
    if ($result) {
        // client deleted successfully
        $response["error"] = false;
        $response["message"] = "Task deleted succesfully";
    } else {
        // client failed to delete
        $response["error"] = true;
        $response["message"] = "Task failed to delete. Please try again!";
    }
    echoRespnse(200, $response);
});



/**
 * job category
 * url - /create_job_category
 * method - POST
 */
$app->post('/create_job_category', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('job_type','description_text'));
    $response = array();

    // reading post params
    $job_type = $app->request->post('job_type');
    $description_text = $app->request->post('description_text');

    $db = new DbHandler();
    $res = $db->createJob_category($job_type, $description_text);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Job category successfully created";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred";
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Sorry, job category already made";
    }
    // echo json response
    echoRespnse(201, $response);
});

/**
 * Job Description
 * url - /create_job_description
 * method - POST
 */
$app->post('/create_job_description', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('text','quantity', 'image', 'amount'));
    $response = array();

        // reading post params
        $text = $app->request->post('text');
        $quantity = $app->request->post('quantity');
        $image = $app->request->post('image');
        $amount = $app->request->post('amount');

        $db = new DbHandler();
        $res = $db->createJob_description($text, $quantity, $image, $amount);

        if  ($res == USER_CREATED_SUCCESSFULLY) {
            $response["error"] = false;
            $response["message"] = "Job description successfully made";
        } else if ($res == USER_CREATE_FAILED) {
            $response["error"] = true;
            $response["message"] = "Oops! An error occurred while posting";
        }
    // echo json response
    echoRespnse(201, $response);
});

/**
 * Create a payment
 * url - /make_payment
 * method - POST
 */
$app->post('/make_payment', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('transaction_reference','payment_time', 'payment_date', 'job_description_id'));
    $response = array();

    // reading post params
    $transaction_reference = $app->request->post('transaction_reference');
    $payment_time = $app->request->post('payment_time');
    $payment_date = $app->request->post('payment_date');
    $job_description_id = $app->request->post('job_description_id');

    $db = new DbHandler();
    $res = $db->create_payment($transaction_reference, $payment_time, $payment_date, $job_description_id);

    if  ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Payment successfully made";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while making Payment";
    }
    // echo json response
    echoRespnse(201, $response);
});


/**
 * Listing single professional
 * method GET
 * url /professional/:id
 * Will return 404 if the professional doesn't exist
 */
$app->get('/professional/:id', 'authenticate', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->getProfessional($proff_id);
    if ($result != NULL) {
        $response["error"] = false;
        $response['proff_id'] = $result['proff_id'];
        $response['proff_name'] = $result['proff_name'];
        $response['email'] = $result['email'];
        $response['cell_no'] = $result['cell_no'];
        $response['national_id'] = $result['national_id'];
        $response['location'] = $result['location'];
        $response['availability_status'] = $result['availability_status'];
        $response['image'] = $result['image'];
        $response['first_name'] = $result['first_name'];
        $response['last_name'] = $result['last_name'];
        $response['gender'] = $result['gender'];
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

/**
 * Listing all job posts
 * method GET
 * url /job_posts
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/job_posts', 'authenticated', function() {
    $response = array();
    $db = new DbHandler();

    // fetch all job posts
    $job_posts = $db->getAllJobPosts();
    if ($job_posts != NULL) {
        $response["error"] = false;
        $response["message"] = "Jobs available.";
        $response["job_posts"] = array();

        // looping through result and preparing job posts array
        while ($jobs = $job_posts->fetch_assoc()) {
            $tmp = array();
            $tmp['job_post_id'] = $jobs['job_post_id'];
            $tmp['client_id'] = $jobs['client_id'];
            $tmp['location'] = $jobs['location'];
            $tmp['apartment_name'] = $jobs['apartment_name'];
            $tmp['house_no'] = $jobs['house_no'];
            $tmp['contact_cell_no'] = $jobs['contact_cell_no'];
            $tmp['job_date'] = $jobs['job_date'];
            $tmp['job_time'] = $jobs['job_time'];
            $tmp['job category'] = $jobs['job category'];
            $tmp['proff_id'] = $jobs['proff_id'];
            $tmp['created_at'] = $jobs['created_at'];
            array_push($response["job_posts"], $tmp);
        }
        // echo json response
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "No jobs available.";
        echoRespnse(404, $response);
    }

});


/**
 * Listing all clients_rating
 * method GET
 * url /clients
 */
$app->get('/clients_rating', 'authenticated', function () {
    $response = array();
    $db = new DbHandler();

    //fetch all clients rating
    $result = $db->getAllClients_rating();
    if($result != NULL)
    {
        $response['error'] = false;
        $response['message'] = "Clients rating exist";
        $response['clients_rating'] = array();

        //loop through thr results and prepare the client_rating array
        while ($client_rating = $result->fetch_assoc()) {
            $tmp = array();
            $tmp['client_rating_id'] = $client_rating['client_rating_id'];
            $tmp['client_id'] = $client_rating['client_id'];
            $tmp['proff_id'] = $client_rating['proff_id'];
            $tmp['rating'] = $client_rating['rating'];
            array_push($response['clients_rating'], $tmp);
        }
        echoRespnse( 200, $response);
    }
    else{
        $response['error'] = true;
        $response['message'] = "Clients rating do not exist";
        echoRespnse(404, $response);
    }
});


/**
 * Listing single client_rating
 * method GET
 * url /client_rating/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/client_rating/:id', 'authenticate', function($client_id) {
//            global $client_id;
    $response = array();
    $db = new DbHandler();

    // fetch single client
    $result = $db->getClient_rating($client_id);

    if ($result != NULL) {
        $response["error"] = false;
        $response["client_id"] = $result["client_id"];
        $response["proff_id"] = $result["proff_id"];
        $response["client_rating_id"] = $result["client_rating_id"];
        $response["rating"] = $result["rating"];
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});


/**
 * Updating an existing client_rating using client_id
 * method PUT
 * params task, status
 * url - /client_rating/:id
 */
$app->put('/client_rating/:id', 'authenticate', function($client_id) use($app) {
    // check for required params
    verifyRequiredParams(array('rating'));

//    global $client_id;
    $rating = $app->request->put('rating');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->updateClient_rating($rating, $client_id);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Client_rating updated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Client_rating failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Client Rating
 * url - /create_client_rating
 * method - POST
 */
$app->post('/create_client_rating', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('rating'));
    $response = array();

    // reading post params
    $rating = $app->request->post('rating');
//    $client_id = $app->request->post('client_id');

    $db = new DbHandler();
    $res = $db->create_Client_rating($rating);

    if  ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "Client rating successful";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred ";
    }
    // echo json response
    echoRespnse(201, $response);
});

/**
 * Listing job posts per client
 * method GET
 * url/job_post/:id
 */
$app->get('/job_post/:id', 'authenticated', function($client_id) {
//            global $client_id;
    $response = array();
    $db = new DbHandler();

    // fetch single client
    $result = $db->getJobPost($client_id);
    $response["error"] = false;
    $response["message"] = "Job exists";
    $response['job_post'] = array();

    if ($result != NULL) {
        $tmp = array();
        $tmp["client_id"] = $result["client_id"];
        $tmp["job_post_id"] = $result["job_post_id"];
        $tmp["apartment_name"] = $result["apartment_name"];
        $tmp["contact_cell_no"] = $result["contact_cell_no"];
        $tmp["house_no"] = $result["house_no"];
        $tmp["location"] = $result["location"];
        $tmp["job_date"] = $result["job_date"];
        $tmp["job_time"] = $result["job_time"];
        $tmp["job_category"] = $result["job_category"];
        $tmp["createdAt"] = $result["created_at"];

        array_push($response['job_post'], $tmp);
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Job doesn't exists";
        echoRespnse(404, $response);
    }
});

/**
 * Listing all categories
 * method GET
 * url /categories
 */
$app->get('/categories', 'authenticated', function () {
    $response = array();
    $db = new DbHandler();

    //fetch all clients rating
    $result = $db->getAllCategories();

    if ($result != NULL) {
        $response['error'] = false;
        $response["message"] = "Categories exist";
        $response['categories'] = array();
        //loop through thr results and prepare the CATEGORIES array
        while ($category = $result->fetch_assoc()) {
            $tmp = array();
            $tmp['category_id'] = $category['category_id'];
            $tmp['category'] = $category['category'];
            array_push($response['categories'], $tmp);
        }
        echoRespnse( 200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Categories doesn't exist";
        echoRespnse(404, $response);
    }
});

/**
 * Listing all job categories
 * method GET
 * url /job_categories
 */
$app->get('/job_categories', 'authenticated', function () {
    $response = array();
    $db = new DbHandler();

    //fetch all clients rating
    $result = $db->getAllJob_Categories();
    if ($result != NULL)
    {
        $response['error'] = false;
        $response['message'] = "Job categories exist";
        $response['job_categories'] = array();

        //loop through thr results and prepare the JOB_CATEGORIES array
        while ($job_category = $result->fetch_assoc()) {
            $tmp = array();
            $tmp['error'] = false;
            $tmp['job_categories_id'] = $job_category['job_categories_id'];
            $tmp['job_type'] = $job_category['job_type'];
            $tmp['description_text'] = $job_category['description_text'];
            $tmp['proff_id'] = $job_category['proff_id'];
            array_push($response['job_categories'], $tmp);
        }
        echoRespnse( 200, $response);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Job categories do not exist";
        echoRespnse(404, $response);
    }
    
});

/**
 * Listing all categories per proff
 * method GET
 * url /job_category/:id
 */
$app->get('/job_category/:id', 'authenticated', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch single client
    $result = $db->getJob_category($proff_id);
    $response["error"] = false;
    $response["message"] = "Job category exist";
    $response['job_category'] = array();

    if ($result != NULL) {
        $tmp = array();
        $tmp["error"] = false;
        $tmp["message"] = "Job category  exist";
        $tmp["job_categories_id"] = $result["job_categories_id"];
        $tmp["job_type"] = $result["job_type"];
        $tmp["description_text"] = $result["description_text"];
        $tmp["proff_id"] = $result["proff_id"];

        array_push($response['job_category'], $tmp);
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Job category doesn't exist";
        echoRespnse(404, $response);
    }
});

/**
 * Listing all job descriptions
 * method GET
 * url /job_category/:id
 */
$app->get('/jobs_description', 'authenticated', function () {
    $response = array();
    $db = new DbHandler();

    //fetch all clients rating
    $result = $db->getAllJobs_description();
    if($result != NULL)
    {
        $response['error'] = false;
        $response['message'] = "Job descriptions exist";
        $response['jobs_description'] = array();

        //loop through thr results and prepare the job_description array
        while ($job_description = $result->fetch_assoc()) {
            $tmp = array();
            $tmp['job_description_id'] = $job_description['job_description_id'];
            $tmp['job_post_id'] = $job_description['job_post_id'];
            $tmp['text'] = $job_description['text'];
            $tmp['quantity'] = $job_description['quantity'];
            $tmp['image'] = $job_description['image'];
            $tmp['amount'] = $job_description['amount'];
            array_push($response['jobs_description'], $tmp);
        }
        echoRespnse(200, $result);
    }
    else
    {
        $response['error'] = true;
        $response['message'] = "Job descriptions do not exist";
        echoRespnse(404, $response);
    }
});


/**
 * Listing job descriptions per id
 * method GET
 * url /job_description/:id
 */
$app->get('/job_description/:id', 'authenticated', function($job_post_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch single client
    $result = $db->getJob_description($job_post_id);
    $response["error"] = true;
    $response["message"] = "Job description exist";
    $response['job_description'] = array();

    if ($result != NULL) {
        $tmp = array();
        $tmp["job_description_id"] = $result["job_description_id"];
        $tmp["job_post_id"] = $result["job_post_id"];
        $tmp["text"] = $result["text"];
        $tmp["quantity"] = $result["quantity"];
        $tmp["image"] = $result["image"];
        $tmp["amount"] = $result["amount"];
        array_push($response['job_description'], $tmp);
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Job description doesn't exists";
        echoRespnse(404, $response);
    }
});

/**
 * Listing all payments
 * method GET
 * url /payments
 */
$app->get('/payments', 'authenticated', function () {
    $response = array();
    $db = new DbHandler();

    //fetch all clients rating
    $result = $db->getAllPayments();
    if($result != NULL)
    {
        $response['error'] = false;
        $response["message"] = "Payment exist";
        $response['payments'] = array();

        //loop through thr results and prepare the payments array
        while ($payment = $result->fetch_assoc()) {
            $tmp = array();
            $tmp['error'] = false;
            $tmp['payment_id'] = $payment['payment_id'];
            $tmp['client_id'] = $payment['client_id'];
            $tmp['proff_id'] = $payment['proff_id'];
            $tmp['job_post_id'] = $payment['job_post_id'];
            $tmp['job_description_id'] = $payment['job_description_id'];
            $tmp['payment_date'] = $payment['payment_date'];
            $tmp['payment_time'] = $payment['payment_time'];
            $tmp['transaction_reference'] = $payment['transaction_reference'];
            array_push($response['payments'], $tmp);
        }
        echoRespnse( 200, $response);
    }
    else
    {
        $response["error"] = true;
        $response["message"] = "Payment doesn't exist";
        echoRespnse(404, $response);
    }
});
/**
 * Listing  payments associated with a proff
 * method GET
 * url /payment_[roff/:id
 */

$app->get('/payment_proff/:id', 'authenticated', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch single client
    $result = $db->getProff_Payment($proff_id);
    $response["error"] = false;
    $response["message"] = "Professional payment record exist";
    $response['payments'] = array();

    if ($result != NULL) {
        $tmp = array();
        $tmp["payment_id"] = $result["payment_id"];
        $tmp["client_id"] = $result["client_id"];
        $tmp["proff_id"] = $result["proff_id"];
        $tmp["job_post_id"] = $result["job_post_id"];
        $tmp["job_description_id"] = $result["job_description_id"];
        $tmp["payment_date"] = $result["payment_date"];
        $tmp["payment_time"] = $result["payment_time"];
        $tmp["transaction_reference"] = $result["transaction_reference"];
        array_push($response['payments'], $tmp);
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "professional payment recored doesn't exist";
        echoRespnse(404, $response);
    }
});

/**
 * Listing  payments associated with a client
 * method GET
 * url /payment_client:id
 */

$app->get('/payment_client/:id', 'authenticated', function($client_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch single client
    $result = $db->getClient_Payment($client_id);
    $response["error"] = false;
    $response["message"] = "Client payment record exist";
    $response['payments'] = array();

    if ($result != NULL) {
        $tmp = array();
        $tmp["payment_id"] = $result["payment_id"];
        $tmp["client_id"] = $result["client_id"];
        $tmp["proff_id"] = $result["proff_id"];
        $tmp["job_post_id"] = $result["job_post_id"];
        $tmp["job_description_id"] = $result["job_description_id"];
        $tmp["payment_date"] = $result["payment_date"];
        $tmp["payment_time"] = $result["payment_time"];
        $tmp["transaction_reference"] = $result["transaction_reference"];
        array_push($response['payments'], $tmp);
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Client payment record doesn't exist";
        echoRespnse(404, $response);
    }
});

/**
 * Listing all job_categories a professional has signed up for
 * method GET
 * url /proffs_jobcategories/:id
 */
$app->get('/proff_jobcategories/:id', 'authenticated', function($proff_id) {
//    global $client_id;
    $response = array();
    $db = new DbHandler();

    // fetching all job_categories for a proff
    $result = $db->getClient_JobCategories($proff_id);
    $response["error"] = false;
    $response["message"] = "Professional job categories exist";
    $response["job_categories"] = array();
    if ($result != NULL)
    {
        // looping through result and preparing clients array
        while ($job_cats = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["job_type"] = $job_cats["job_type"];
            $tmp["job_categories_id"] = $job_cats["job_categories_id"];
            $tmp["description_text"] = $job_cats["description_text"];
            $tmp["proff_id"] = $job_cats["proff_id"];
    //        $tmp["cell_no"] = $job_cats["cell_no"];
    //        $tmp["location"] = $job_cats["location"];
    //        $tmp["image"] = $job_cats["image"];
    //        $tmp["email"] = $job_cats["email"];
    //        $tmp["status"] = $job_cats["status"];
    //        $tmp["createdAt"] = $job_cats["created_at"];
            array_push($response["job_categories"], $tmp);
        }
        echoRespnse(200, $response);
    }
    else
    {
        $response["error"] = true;
        $response["message"] = "Professional job categories do not exist";
        echoRespnse(404, $response);
    }

});


/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/clients/:id', 'authenticate', function($task_id) use($app) {
            // check for required params
            verifyRequiredParams(array('task', 'status'));

            global $client_id;
            $task = $app->request->put('task');
            $status = $app->request->put('status');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateTask($client_id, $task_id, $task, $status);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });

$app->run();
?>


