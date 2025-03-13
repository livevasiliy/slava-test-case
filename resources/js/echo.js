import Echo from 'laravel-echo';
import socket from 'socket.io-client';

window.io = socket;
window.Echo = new Echo({
    broadcaster : 'socket.io',
    host: '0.0.0.0:6001',
});

