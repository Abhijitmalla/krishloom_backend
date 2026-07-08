<?php

require_once __DIR__ . '/../config/Database.php';

class Affiliate
{
    private $conn;
    private $table = "affiliates";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Get All Affiliates
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $affiliates = [];
        while ($row = $result->fetch_assoc()) {
            $affiliates[] = $row;
        }
        
        return $affiliates;
    }

    // Get Affiliate By ID
    public function getById($id)
    {
        $query = "SELECT id, membership_id, name, email, mobile_no, alternate_mobile, gender, dob, state, city, pin_code, shipping_address, profile_pic, aadhar_no, pan_no, bank_account, ifsc_code, kyc_verified, created_at FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update Personal Details
    public function updateProfile($id, $data)
    {
        $query = "
            UPDATE {$this->table}
            SET name = ?, email = ?, alternate_mobile = ?, gender = ?, dob = ?,
                state = ?, city = ?, pin_code = ?, shipping_address = ?
            WHERE id = ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssssissi",
            $data['name'],
            $data['email'],
            $data['alternate_mobile'],
            $data['gender'],
            $data['dob'],
            $data['state'],
            $data['city'],
            $data['pin_code'],
            $data['shipping_address'],
            $id
        );
        return $stmt->execute();
    }

    // Update Profile Picture
    public function updateProfilePic($id, $picUrl)
    {
        $query = "UPDATE {$this->table} SET profile_pic = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $picUrl, $id);
        return $stmt->execute();
    }

    // Register Affiliate
    public function register($data)
    {
        $query = "
            INSERT INTO {$this->table}
            (
               membership_id,
               name,
               mobile_no,
               email,
               shipping_address,
               state,
               city,
               pin_code,
               password
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param(
            "sssssssis",
            $data['membership_id'],
            $data['name'],
            $data['mobile_no'],
            $data['email'],
            $data['shipping_address'],
            $data['state'],
            $data['city'],
            $data['pin_code'],
            $data['password']
        );

        return $stmt->execute();
    }

    public function saveOTP($data, $otp)
    {
        $expiry = $this->getOtpExpiry();
        $data['mobile_no'] = $this->normalizeDigits($data['mobile_no']);
        $data['pin_code'] = $this->normalizeDigits($data['pin_code']);
        $otp = $this->normalizeDigits($otp);

        $this->deletePendingOtp($data['mobile_no']);

        $query = "INSERT INTO affiliate_otps
        (
            membership_id,
            name,
            mobile_no,
            email,
            shipping_address,
            state,
            city,
            pin_code,
            password,
            otp,
            otp_expiry
        )
        VALUES(?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param(
            "sssssssisss",
            $data['membership_id'],
            $data['name'],
            $data['mobile_no'],
            $data['email'],
            $data['shipping_address'],
            $data['state'],
            $data['city'],
            $data['pin_code'],
            $data['password'],
            $otp,
            $expiry
        );

        return $stmt->execute();
    }

    public function verifyOTP($mobile, $otp)
    {
        $mobile = $this->normalizeDigits($mobile);
        $otp = $this->normalizeDigits($otp);

        $query = "SELECT * FROM affiliate_otps
        WHERE mobile_no=?
        AND otp=?
        AND otp_expiry>=NOW()
        LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $mobile, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return false;
        }

        $row = $result->fetch_assoc();

        if (!$this->register($row)) {
            return false;
        }

        $delete = $this->conn->prepare(
            "DELETE FROM affiliate_otps WHERE id=?"
        );
        $delete->bind_param("i", $row['id']);
        $delete->execute();

        return true;
    }

    // Find by Membership ID or Mobile Number
    public function findByLogin($login)
    {
        $query = "
            SELECT *
            FROM {$this->table}
            WHERE membership_id = ?
            OR mobile_no = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Check if Membership ID already exists
    public function membershipExists($membershipId)
    {
        $query = "
            SELECT id
            FROM {$this->table}
            WHERE membership_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $membershipId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Check if Mobile Number already exists
    public function mobileExists($mobile)
    {
        $query = "
            SELECT id
            FROM {$this->table}
            WHERE mobile_no = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Check if Email already exists
    public function emailExists($email)
    {
        $query = "
            SELECT id
            FROM {$this->table}
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Delete a pending OTP row (rollback if SMS delivery fails)
    public function deletePendingOtp($mobile)
    {
        $mobile = $this->normalizeDigits($mobile);

        $stmt = $this->conn->prepare(
            "DELETE FROM affiliate_otps WHERE mobile_no = ?"
        );
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
    }

    private function normalizeDigits($value)
    {
        return preg_replace('/\D+/', '', (string)$value);
    }

    private function getOtpExpiry()
    {
        $result = $this->conn->query("SELECT DATE_ADD(NOW(), INTERVAL 5 MINUTE) AS otp_expiry");
        $row = $result->fetch_assoc();
        return $row['otp_expiry'];
    }

    // Update KYC Details
    public function updateKYC($id, $aadhar, $pan, $account, $ifsc)
    {
        $query = "
            UPDATE {$this->table}
            SET aadhar_no = ?, pan_no = ?, bank_account = ?, ifsc_code = ?, kyc_verified = 1
            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $aadhar, $pan, $account, $ifsc, $id);
        
        return $stmt->execute();
    }

    public function saveAadharOTP($aadhar_no, $otp)
    {
        $this->conn->query("DELETE FROM aadhar_otps WHERE aadhar_no = '{$this->conn->real_escape_string($aadhar_no)}'");
        
        $query = "INSERT INTO aadhar_otps (aadhar_no, otp) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $aadhar_no, $otp);
        return $stmt->execute();
    }

    public function verifyAadharOTP($aadhar_no, $otp)
    {
        $query = "SELECT * FROM aadhar_otps WHERE aadhar_no = ? AND otp = ? AND created_at >= NOW() - INTERVAL 5 MINUTE LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $aadhar_no, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $this->conn->query("DELETE FROM aadhar_otps WHERE aadhar_no = '{$this->conn->real_escape_string($aadhar_no)}'");
            return true;
        }
        return false;
    }
}
