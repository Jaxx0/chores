<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `Clients` table method ------------------ */
    /**
     * Creating new client
     * @param String $first_name and $last_name
     * @param String $email User login email id
     * @param String $password User login password
     */

    public function createClient($first_name, $last_name, $email, $password) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already exists in the db
        if (!$this->isClientExists($email)) {
            // Generating password hash
            $passwd = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO clients(first_name, last_name, email, passwd, api_key, status) values(?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $passwd, $api_key );
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
        return $response;
    }

    /**
     * Creating new job post
     */
    public function createJob_post($location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already exists in the db
        if (!$this->isClientExists($contact_cell_no)) {

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO job_post(location, apartment_name, house_no, contact_cell_no, job_date, job_time, job_category) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiisss", $location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category );
            $result = $stmt->execute();
            $stmt->close();


            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
        return $response;
    }

    /**
     * Creating new job category
     */
    public function createJob_category($job_type, $description_text) {
        $response = array();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO job_categories(job_type, description_text) values(?, ?)");
        $stmt->bind_param("ss", $job_type, $description_text);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // job_post successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create job_post
            return USER_CREATE_FAILED;
        }

        return $response;
    }
    /**
     * Creating new job post
     */
    public function createJob_description($text, $quantity, $image, $amount) {
        $response = array();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO job_description(text, quantity, image, amount) values(?, ?, ?, ?)");
            $stmt->bind_param("ssss", $text, $quantity, $image, $amount );
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // job_post successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create job_post
                return USER_CREATE_FAILED;
            }

        return $response;
    }

    /**
     * Creating a payment record
     */
    public function create_payment($transaction_reference, $payment_time, $payment_date, $job_description_id) {
        $response = array();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO payments(transaction_reference, payment_time, payment_date, job_description_id) values(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $transaction_reference, $payment_time, $payment_date, $job_description_id );
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // payment successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create payment
            return USER_CREATE_FAILED;
        }

        return $response;
    }

    /**
     * Checking Client login
     * @param String $email Client login email id
     * @param String $passwd Client login password
     * @return boolean Client login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching Client by email
        $stmt = $this->conn->prepare("SELECT passwd FROM clients WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($passwd);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found Client with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($passwd, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking for duplicate Client by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isClientExists($email) {
        $stmt = $this->conn->prepare("SELECT client_id from clients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching Client by email
     * @param String $email Client email id
     */
    public function getClientByEmail($email) {
        $stmt = $this->conn->prepare("SELECT first_name, last_name, email, api_key, status, created_at FROM clients WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $first_name, $last_name, $email, $api_key, $status, $created_at);
            $stmt->fetch();
            $user = array();
//            $user["client_name"] = $client_name;
            $user["first_name"] = $first_name;
            $user["last_name"] = $last_name;
            $user["email"] = $email;
//            $user["cell_no"] = $cell_no;
//            $user["location"] = $location;
//            $user["image"] = $image;
            $user["api_key"] = $api_key;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching Client api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($client_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM clients WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching client id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {

        $stmt = $this->conn->prepare("SELECT client_id FROM clients WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $client_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $client_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT client_id from clients WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key

    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
     *
     *
     */

    /* ------------- `tasks` table method ------------------ */

    /**
     * Creating new task
     * @param String $user_id user id to whom task belongs to
     * @param String $task task text
     */
    public function createTask($user_id, $task) {
        $stmt = $this->conn->prepare("INSERT INTO tasks(task) VALUES(?)");
        $stmt->bind_param("s", $task);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // task row created
            // now assign the task to user
            $new_task_id = $this->conn->insert_id;
            $res = $this->createUserTask($user_id, $new_task_id);
            if ($res) {
                // task created successfully
                return $new_task_id;
            } else {
                // task failed to create
                return NULL;
            }
        } else {
            // task failed to create
            return NULL;
        }
    }

    /**
     * Fetching single Proffesional
     * @param String $task_id id of the task


    public function getProffesional($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }
     *
     *
     */


    /**
     * Fetching single Client
     */
    public function getClient($client_id) {
        $stmt = $this->conn->prepare("SELECT * FROM clients WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        if ($stmt->execute()) {
            $client = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $client;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching job posts per client
     */
    public function getJobPost($client_id) {
        $stmt = $this->conn->prepare("SELECT * FROM job_post WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        if ($stmt->execute()) {
            $jobpost = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $jobpost;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching All job posts
     */
    public function getAllJobPosts() {
        $stmt = $this->conn->prepare("SELECT * FROM job_post");
        $stmt->execute();
        $job_posts = $stmt->get_result();
        $stmt->close();
        return $job_posts;
    }


    /**
     * Fetching all clients
     * @param String $client_id id of the user
     */
    public function getAllClients() {
        $stmt = $this->conn->prepare("SELECT * FROM clients");
        $stmt->execute();
        $clients = $stmt->get_result();
        $stmt->close();
        return $clients;
    }

    /**
     * Fetching all clients Ratings
     */
    public function getAllClients_rating() {
        $stmt = $this->conn->prepare("SELECT * FROM client_rating");
        $stmt->execute();
        $client_rating = $stmt->get_result();
        $stmt->close();
        return $client_rating;
    }

    /**
     * Fetching single client Rating
     */
    public function getClient_rating($client_id) {
        $stmt = $this->conn->prepare("SELECT * FROM client_rating WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        if ($stmt->execute()) {
            $client_rating = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $client_rating;
        } else {
            return NULL;
        }
    }

    /**
     * Updating single client Rating
     */
    public function updateClient_rating($rating, $client_id) {
        $stmt = $this->conn->prepare("UPDATE client_rating set rating = ? WHERE client_id = ?");
        $stmt->bind_param("si", $rating, $client_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Creating a payment record
     */
    public function create_Client_rating($rating) {
        $response = array();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO client_rating(rating) values(?)");
        $stmt->bind_param("s", $rating );
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // rating successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create rating
            return USER_CREATE_FAILED;
        }

        return $response;
    }

    /**
     * Fetching all categories
     */
    public function getAllCategories() {
        $stmt = $this->conn->prepare("SELECT * FROM categories");
        $stmt->execute();
        $category= $stmt->get_result();
        $stmt->close();
        return $category;
    }


    /**
     * Fetching all Job categories
     */
    public function getAllJob_Categories() {
        $stmt = $this->conn->prepare("SELECT * FROM job_categories");
        $stmt->execute();
        $job_category= $stmt->get_result();
        $stmt->close();
        return $job_category;
    }


    /**
     * Fetching Job categories per proff
     */
    public function getJob_category($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM job_categories WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $job_category = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $job_category;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching All Job Descriptions
     */
    public function getAllJobs_description() {
        $stmt = $this->conn->prepare("SELECT * FROM job_description");
        $stmt->execute();
        $job_description= $stmt->get_result();
        $stmt->close();
        return $job_description;
    }

    /**
     * Fetching All Payments
     */
    public function getAllPayments() {
        $stmt = $this->conn->prepare("SELECT * FROM payments");
        $stmt->execute();
        $payment= $stmt->get_result();
        $stmt->close();
        return $payment;
    }

    /**
     * Fetching Job Descriptions per id
     */
    public function getJob_description($job_post_id) {
        $stmt = $this->conn->prepare("SELECT * FROM job_description WHERE job_post_id = ?");
        $stmt->bind_param("i", $job_post_id);
        if ($stmt->execute()) {
            $job_desc = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $job_desc;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching all payments associated with a proff
     */
    public function getProff_Payment($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $payment = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $payment;
        } else {
            return NULL;
        }
    }


    /**
     * Fetching all payments per client
     */
    public function getClient_Payment($client_id) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        if ($stmt->execute()) {
            $payment = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $payment;
        } else {
            return NULL;
        }
    }

    /**
     * Updating clients
     * @param String $client_id id of the task
     */
    public function updateClient( $client_name, $first_name,$last_name, $cell_no, $location, $image, $status, $client_id) {
        $stmt = $this->conn->prepare("UPDATE clients set client_name = ?, first_name = ?, last_name = ?, cell_no = ?, location = ?, image = ?, status = ? WHERE client_id = ?");
        $stmt->bind_param("sssssssi", $client_name, $first_name, $last_name, $cell_no, $location, $image, $status, $client_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating a job post using client_id
     * @param String $client_id id of the task
     */
    public function updateJob_PostClient( $location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category, $proff_id, $client_id, $job_post_id) {
        $stmt = $this->conn->prepare("UPDATE job_post set location = ?, apartment_name = ?, house_no = ?, contact_cell_no = ?, job_date = ?, job_time = ?, job_category = ? WHERE client_id = ?");
        $stmt->bind_param("sssssssi", $location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category, $client_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating a job post using proff_id
     * @param String $client_id id of the task
     */
    public function updateJob_PostProff($location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category, $proff_id,  $job_post_id) {
        $stmt = $this->conn->prepare("UPDATE job_post set location = ?, apartment_name = ?, house_no = ?, contact_cell_no = ?, job_date = ?, job_time = ?, job_category = ? WHERE proff_id = ?");
        $stmt->bind_param("sssssssi", $location, $apartment_name, $house_no, $contact_cell_no, $job_date, $job_time, $job_category, $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deleting a client
     * @param String $client-id id of the client to delete
     */
    public function deleteClient($client_id) {
        $stmt = $this->conn->prepare("DELETE FROM clients WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /**
     * Deactivate a client
     * @param String $client_id id of the client
     */
    public function DeactivateClient($status, $client_id) {
        $stmt = $this->conn->prepare("UPDATE clients set status = ?  WHERE client_id = ?");
        $stmt->bind_param("ii", $status, $client_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deleting a job_post per client
     * @param String $client-id id of the job_post to delete
     */
    public function delete_job_post_Client($client_id) {
        $stmt = $this->conn->prepare("DELETE FROM job_post WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /**
     * Deleting a job_post per proff
     * @param String $proff_id id of the job_post to delete
     */
    public function delete_job_post_Proff($proff_id) {
        $stmt = $this->conn->prepare("DELETE FROM job_post WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /* ------------- `proffesionals` table method ------------------ */

    /**
     * Creating new proffesional
     * @param String $first_name proffesional first name
     * @param String $las_name proffesional last name
     * @param String $email proffesional login email id
     * @param String $password proffesional login password
     */
    public function createProffesional($first_name, $last_name, $email, $password) {
        require_once 'PassHash.php';

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $passwd = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO proffesionals(first_name, last_name, email, passwd, api_key, status) values(?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $passwd, $api_key);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
    }

    /**
     * Checking proffesional login
     * @param String $email proffesional login email id
     * @param String $password proffesional login password
     * @return boolean proffesional login status success/fail
     */
    public function checkProffLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT passwd FROM proffesionals WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($passwd);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($passwd, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Change proffesional password
     * @param String $password proffesional login password
     */
    public function changeProffesionalPassword($password, $proff_id) {
        require_once 'PassHash.php';

        // Generating password hash
        $passwd = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generateApiKey();

        // insert query
        $stmt = $this->conn->prepare("UPDATE proffesionals set passwd = ?, api_key = ? WHERE proff_id = ?");
        $stmt->bind_param("ssi", $passwd, $api_key, $proff_id);

        $result = $stmt->execute();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // User successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create user
            return USER_CREATE_FAILED;
        }
    }

    /**
     * Checking for duplicate proffesional by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT proff_id from proffesionals WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching proffesional by email
     * @param String $email proffesional email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT proff_id, proff_name, api_key, email, cell_no, national_id, location, availability_status, image, first_name, last_name, gender, status, created_at FROM proffesionals WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching proffesional api key
     * @param String $proff_id proffesional id primary key in proffesionals table
     */
    public function getProffApiKeyById($proff_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $api_key = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching proffesional id by api key
     * @param String $api_key proffesional api key
     */
    public function getProffId($api_key) {
        $stmt = $this->conn->prepare("SELECT proff_id FROM proffesionals WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $proff_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proff_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating proffesional api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key proffesional api key
     * @return boolean
     */
    public function isValidProffApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT proff_id from proffesionals WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* ------------- `proffesionals` table method ------------------ */

    /**
     * Fetching proffessional details
     * @param String $proff_id of the proffesional
     */
    public function getProffesional($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }
    /**
     * Fetching all proffesionals
     * @param String $proff_id of the proffesional
     */
    public function getAllproffesionals() {
        $stmt = $this->conn->prepare("SELECT * FROM proffesionals");
        $stmt->execute();
        $proffesionals = $stmt->get_result();
        $stmt->close();
        return $proffesionals;
    }

    /**
     * Updating proffesional
     * @param String $proff_id id of the proffesional
     * @param String proff_name text
     * @param String $cell_no text
     * @param String $national_id text
     * @param String $location text
     * @param String $image text
     * @param String $first_name text
     * @param String $last_name text
     */
    public function updateProffesional($proff_name, $cell_no, $national_id, $location, $image, $first_name, $last_name, $gender, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesionals set proff_name = ?, cell_no = ?, national_id = ?, location = ?, image = ?, first_name = ?, last_name = ?, gender = ? WHERE proff_id = ?");
        $stmt->bind_param("ssssssssi", $proff_name, $cell_no, $national_id, $location, $image, $first_name, $last_name, $gender, $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deactivate proffesional account
     * @param String $proff_id id of the proffesional
     * @param String status number
     */
    public function deactivate_activateProffesional($status, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesionals set status = ? WHERE proff_id = ?");
        $stmt->bind_param("si", $status, $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Change proffesional availability
     * @param String $proff_id id of the proffesional
     * @param String availability_status number
     */
    public function changeProffesionalAvailability($availability_status, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesionals set availability_status = ? WHERE proff_id = ?");
        $stmt->bind_param("ii", $availability_status, $proff_id);
        $result = $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($num_affected_rows > 0)
        {
            // Check for successful insertion
            if ($result) {
                // Proffesional status successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create proffesional status
                return USER_CREATE_FAILED;
            }
        }
    }

    /**
     * Deleting a proffesional
     * @param String $proff_id id of the proffesional to delete
     */
    public function deleteProffesional($proff_id) {
        $stmt = $this->conn->prepare("DELETE FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /** ------------- `proffesional_status` table method ------------------ */

    /**
     * Creating new proffesional status
     * @param String $proff_id proffesional proff_id
     */
    public function createProffesionalStatus($proff_id) {

        // First check if proffesional already existed in db
        if (!$this->isProffExists($proff_id)) {

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO proffesional_status(proff_id) values(?)");
            $stmt->bind_param("i", $proff_id);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // Proffesional status successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create proffesional status
                return USER_CREATE_FAILED;
            }
        } else {
            // proffesional status with same $proff_id already existed in the db
            return USER_ALREADY_EXISTED;
        }
    }
    private function isProffExists($proff_id) {
        $stmt = $this->conn->prepare("SELECT proff_id from proffesional_status WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Change proffesional text status
     * @param String $proff_id id of the proffesional status
     * @param String profile text status
     */
    public function changeProffesionalTextStatus($proff_text, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesional_status set proff_text = ? WHERE proff_id = ?");
        $stmt->bind_param("si", $proff_text, $proff_id);
        $result = $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($num_affected_rows > 0)
        {
            // Check for successful insertion
            if ($result) {
                // Proffesional status successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create proffesional status
                return USER_CREATE_FAILED;
            }
        }
    }

    /**
     * Change proffesional image status
     * @param String $proff_id id of the proffesional status
     * @param String profile image status
     */
    public function changeProffesionalImageStatus($proff_image, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesional_status set proff_image = ? WHERE proff_id = ?");
        $stmt->bind_param("si", $proff_image, $proff_id);
        $result = $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($num_affected_rows > 0)
        {
            // Check for successful insertion
            if ($result) {
                // Proffesional status successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create proffesional status
                return USER_CREATE_FAILED;
            }
        }
    }
    /**
     * Change proffesional video status
     * @param String $proff_id id of the proffesional status
     * @param String profile video status
     */
    public function changeProffesionalVideoStatus($proff_video, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesional_status set proff_video = ? WHERE proff_id = ?");
        $stmt->bind_param("si", $proff_video, $proff_id);
        $result = $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($num_affected_rows > 0)
        {
            // Check for successful insertion
            if ($result) {
                // Proffesional status successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create proffesional status
                return USER_CREATE_FAILED;
            }
        }
    }

    /**
     * Creating new proffesional rating
     * @param String $proff_id proffesional proff_id , rating
     */
    public function rateProffesional($client_id, $proff_id, $rating) {

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO proffessional_rating(client_id, proff_id, rating) values(?,?,?)");
        $stmt->bind_param("sis", $client_id, $proff_id, $rating);

        $result = $stmt->execute();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // Proffesional rating successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create proffesional rating
            return USER_CREATE_FAILED;
        }
    }

    /**
     * Fetching proffessional rating
     * @param String $proff_id of the proffesional
     */
    public function getProffesionalRating($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM proffessional_rating WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching proffessional status
     * @param String $proff_id of the proffesional
     */
    public function getProffesionalStatus($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM proffesional_status WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }

}

?>


