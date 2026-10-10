<?php

return [
    'greeting' => 'Merhaba :name,',
    'verify' => [
        'subject' => 'Picture Shop e-posta adresinizi doğrulayın',
        'line' => 'Fotoğraf alıp satmaya başlamak için lütfen e-posta adresinizi doğrulayın.',
        'action' => 'E-postamı doğrula',
        'ignore' => 'Bir hesap oluşturmadıysanız bu e-postayı yok sayabilirsiniz.',
    ],
    'reset' => [
        'subject' => 'Picture Shop şifrenizi sıfırlayın',
        'line' => 'Hesabınız için bir şifre sıfırlama talebi aldığımız için bu e-postayı görüyorsunuz.',
        'action' => 'Şifremi sıfırla',
        'expire' => 'Bu bağlantı :count dakika içinde geçersiz olacak.',
        'ignore' => 'Şifre sıfırlama talebinde bulunmadıysanız bu e-postayı yok sayabilirsiniz.',
    ],
    'order' => [
        'subject' => 'Picture Shop siparişiniz',
        'line' => 'Satın aldığınız için teşekkürler! Fotoğraflarınız indirilmeye hazır:',
        'total' => 'Toplam: $:total',
        'action' => 'İndirmelerime git',
    ],
    'sold' => [
        'subject' => 'Bir fotoğrafınız satıldı',
        'line' => 'Harika haber! ":title" az önce :buyer tarafından satın alındı.',
        'amount' => 'Tutar: $:amount',
    ],
];
