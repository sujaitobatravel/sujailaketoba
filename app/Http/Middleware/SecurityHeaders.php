<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        // frame-src WAJIB sejalan dengan Package::videoEmbedUrl() dan
        // Package::mapEmbedUrl(). Keduanya yang menentukan host apa saja yang
        // bisa muncul sebagai <iframe> di halaman detail paket; host yang lolos
        // di sana tapi tidak terdaftar di sini gagal DIAM-DIAM -- browser cuma
        // menampilkan kotak kelabu "This content is blocked", tanpa error di
        // log server. Menambah penyedia video/peta baru = ubah dua tempat.
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:* https://cdn.jsdelivr.net https://www.youtube.com https://s.ytimg.com; style-src 'self' 'unsafe-inline' http://127.0.0.1:* https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com https://*.google.com https://*.google.co.id https://*.google.com.my; connect-src 'self' http://127.0.0.1:* ws://127.0.0.1:*;");

        return $response;
    }
}
