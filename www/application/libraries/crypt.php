<?php
class Crypt
{
        var $key = NULL;
		var $key_128 = NULL;
        var $iv = NULL;
        var $iv_size = NULL;
 
        function Crypt()
        {
			$this->init();
        }
 
        function init($key = "", $type=0, $salt='!kQm*fF3pXe1Kbm%9')
        {
            $this->key = ($key != "") ? $key : "";
			$this->key_128 = hash('SHA256', $salt . $key, true);
 
			if ($type == 1)
			{
	            $this->algorithm = MCRYPT_RIJNDAEL_128;
	            $this->mode = MCRYPT_MODE_CBC;
			}
			else 
			{
                $this->algorithm = MCRYPT_DES;
                $this->mode = MCRYPT_MODE_ECB;
			}

            $this->iv_size = mcrypt_get_iv_size($this->algorithm, $this->mode);
			
			// Constantly cycle the random number generator
			srand();
			
            $this->iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
            
            return "ok";
        }
 
 		//=============================================================================================================================
 		//
 		// Used for Encrypting and Decrypting Sensitive Data with RIJNDAEL 128 bit AES encryption
 		//
 		function encrypt_128($data, $salt='!kQm*fF3pXE1Kbm%9')
		{
			if (strlen($iv_base64 = rtrim(base64_encode($this->iv), '=')) != 22) 
				return false;
			
			// Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
			$encrypted = base64_encode(mcrypt_encrypt($this->algorithm, $this->key_128, $data . md5($data), MCRYPT_MODE_CBC, $this->iv));

			// We're done!
			return $iv_base64 . $encrypted;
		}
		
		function decrypt_128($data, $salt='!kQm*fF3pXE1Kbm%9')
		{
			// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
			$iv = base64_decode(substr($data, 0, 22) . '==');
			// Remove $iv from $encrypted.
			$encrypted = substr($data, 22);
			// Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
			$decrypted = rtrim(mcrypt_decrypt($this->algorithm, $this->key_128, base64_decode($encrypted), $this->mode, $iv), "\0\4");
			// Retrieve $hash which is the last 32 characters of $decrypted.
			$hash = substr($decrypted, -32);
			// Remove the last 32 characters from $decrypted.
			$decrypted = substr($decrypted, 0, -32);
			// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
			if (md5($decrypted) != $hash) return false;
			// Yay!
			return $decrypted;
		}
		//
 		//=============================================================================================================================
		
        function encrypt($data)
        {
                $size = mcrypt_get_block_size($this->algorithm, $this->mode);
                $data = $this->pkcs5_pad($data, $size);
                return base64_encode(mcrypt_encrypt($this->algorithm, $this->key, $data, $this->mode, $this->iv));
        }
 
        function decrypt($data)
        {
                return $this->pkcs5_unpad(rtrim(mcrypt_decrypt($this->algorithm, $this->key, base64_decode($data), $this->mode, $this->iv)));
        }
 
        function pkcs5_pad($text, $blocksize)
        {
                $pad = $blocksize - (strlen($text) % $blocksize);
                return $text . str_repeat(chr($pad), $pad);
        }
 
        function pkcs5_unpad($text)
        {
                $pad = ord($text{strlen($text)-1});
                if ($pad > strlen($text)) return false;
                if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
                return substr($text, 0, -1 * $pad);
        }
}
 
?>