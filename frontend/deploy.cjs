/**
 * FTP Deploy Script
 * -----------------
 * Reads credentials from .env.deploy (never commit that file).
 * Run:  npm run deploy
 *
 * First time only:  npm install --save-dev ftp-deploy dotenv
 */

const FtpDeploy = require('ftp-deploy');
require('dotenv').config({ path: '.env.deploy' });

const {
    FTP_USER,
    FTP_PASS,
    FTP_HOST = 'itanagarchoice.com',
    FTP_PORT = '21',
    FTP_SECURE = 'true',
    FTP_REMOTE_DIR = '/public_html/frontend',
} = process.env;

if (!FTP_USER || !FTP_PASS) {
    console.error('ERROR: FTP_USER and FTP_PASS must be set in frontend/.env.deploy');
    process.exit(1);
}

const isSecure = FTP_SECURE !== 'false';

const config = {
    user: FTP_USER,
    password: FTP_PASS,
    host: FTP_HOST,
    port: parseInt(FTP_PORT, 10),
    localRoot: __dirname + '/dist',
    remoteRoot: FTP_REMOTE_DIR,
    include: ['*', '**/*'],
    deleteRemote: false,
    forcePasv: true,
    sftp: false,
    secure: isSecure,
    secureOptions: isSecure ? { rejectUnauthorized: false } : undefined,
};

const ftp = new FtpDeploy();

ftp.on('uploading', ({ filename, transferredFileCount, totalFilesCount }) => {
    const pct = Math.round((transferredFileCount / totalFilesCount) * 100);
    process.stdout.write(`\r[${pct}%] Uploading: ${filename}`.padEnd(80));
});

ftp.on('upload-error', ({ err }) => {
    console.error('\nUpload error:', err);
});

console.log(`\nDeploying dist/ → ${FTP_HOST}${FTP_REMOTE_DIR}\n`);

ftp.deploy(config)
    .then(() => console.log('\n\n✓ Deploy complete!'))
    .catch(err => {
        console.error('\n✗ Deploy failed:', err.message || err);
        process.exit(1);
    });
