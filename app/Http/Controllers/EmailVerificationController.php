<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;


class EmailVerificationController extends Controller
{
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        $emailAddress = $request->input('email');

        // Mengekstrak domain dari alamat email
        $domain = substr(strrchr($emailAddress, "@"), 1);

        // Mengambil MX record untuk domain
        $mxRecords = dns_get_record($domain, DNS_MX);
        $mxServers = [];

        // Mendapatkan server MX yang digunakan
        foreach ($mxRecords as $record) {
            $mxServers[] = $record['target'];
        }

        // Memeriksa keberadaan kotak surat pada setiap server MX
        foreach ($mxServers as $mxServer) {
            $server = new Server($mxServer);

            try {
                // Menghubungkan ke server IMAP
                $connection = $server->authenticate('your_email@example.com', 'your_email_password');

                // Mencari email yang dikirim ke alamat yang diberikan
                $mailbox = $connection->getMailbox('INBOX');
                $messages = $mailbox->getMessages(
                    new To($emailAddress),
                    new Text($emailAddress)
                );

                if (count($messages) > 0) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Mailbox exists'
                    ], 200);
                }
            } catch (\Exception $e) {
                // Gagal terhubung ke server atau kesalahan lainnya
                continue;
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Mailbox does not exist'
        ], 404);
    }

    public function verifyMail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        $emailAddress = $request->input('email');
        $emailfrom = "noreply@localhost";

        // Mendapatkan domain dari alamat email
        $domain = substr(strrchr($emailAddress, "@"), 1);

        // Mengambil MX record server untuk domain
        $mxRecords = [];
        getmxrr($domain, $mxRecords);

        // Memeriksa setiap MX record untuk terhubung ke server email
        foreach ($mxRecords as $mxRecord) {
            $host = $mxRecord;
            $port = 25; // Port SMTP default

            // Membuat koneksi ke server email menggunakan protokol SMTP
            $connection = fsockopen($host, $port, $errno, $errstr, 30);

            if ($connection) {
                // Menerima respons dari server email
                fgets($connection, 1024);

                // Mengirim perintah HELO
                fputs($connection, "HELO localhost\r\n");
                fgets($connection, 1024);

                // Mengirim perintah MAIL FROM
                fputs($connection, "MAIL FROM: <$emailfrom>\r\n");
                fgets($connection, 1024);

                // Mengirim perintah RCPT TO untuk alamat email yang akan diperiksa
                fputs($connection, "RCPT TO: <$emailAddress>\r\n");
                $response = fgets($connection, 1024);

                // Menutup koneksi
                fputs($connection, "QUIT\r\n");
                fclose($connection);

                // Memeriksa respons dari server email
                if (strpos($response, "250") === 0) {
                    // Alamat email valid, kotak surat ada
                    // return true;
                    return response()->json([
                        'success' => true,
                        'message' => 'Mailbox exists'
                    ], 200);
                }
            }
        }

        // Alamat email tidak valid, kotak surat tidak ada
        // return false;
        return response()->json([
            'success' => false,
            'message' => 'Mailbox does not exist'
        ], 404);
    }
}
