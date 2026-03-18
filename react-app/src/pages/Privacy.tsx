export default function Privacy() {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <h1 className="font-display font-bold text-3xl text-gray-900 mb-8">Privacy Policy</h1>
      <div className="card p-6 sm:p-8 prose prose-sm max-w-none prose-headings:font-display prose-a:text-brand-600 prose-p:text-gray-600 prose-li:text-gray-600">
        <p>
          Welcome to <strong>theitanagarchoice.com</strong>. Your privacy is important to us. This Privacy Policy explains how Itanagar Choice collects, uses, protects, and handles your personal information when you use our website and services.
        </p>
        <p>
          By accessing or using theitanagarchoice.com, you agree to the terms described in this Privacy Policy.
        </p>

        <h2>1. Information We Collect</h2>
        <p>When you use our platform, we may collect the following information:</p>
        <h3>Personal Information</h3>
        <ul>
          <li>Full Name</li>
          <li>Mobile Number</li>
          <li>Email Address</li>
          <li>Address (if required)</li>
        </ul>
        <h3>Transaction Information</h3>
        <p>When you purchase tickets, we may collect:</p>
        <ul>
          <li>Payment transaction ID</li>
          <li>Payment status</li>
          <li>Ticket purchase details</li>
        </ul>
        <p>Payments are processed securely through third-party payment gateways such as Razorpay, and <strong>we do not store your card or banking details</strong>.</p>
        <h3>Technical Information</h3>
        <p>We may automatically collect:</p>
        <ul>
          <li>IP address</li>
          <li>Device type</li>
          <li>Browser type</li>
          <li>Usage data</li>
        </ul>
        <p>This information helps improve our website performance and security.</p>

        <h2>2. How We Use Your Information</h2>
        <p>Your information may be used for the following purposes:</p>
        <ul>
          <li>Processing ticket purchases</li>
          <li>Confirming transactions</li>
          <li>Sending ticket confirmation and results</li>
          <li>Customer support and communication</li>
          <li>Preventing fraud and misuse</li>
          <li>Improving our services and user experience</li>
        </ul>
        <p>We only use your information for legitimate business purposes.</p>

        <h2>3. Sharing of Information</h2>
        <p>We do not sell or rent your personal information to third parties. Your information may be shared only with:</p>
        <ul>
          <li>Payment gateway providers (for processing payments)</li>
          <li>Legal authorities if required by law</li>
          <li>Technical service providers who help operate our platform</li>
        </ul>
        <p>All partners are required to keep your information confidential.</p>

        <h2>4. Data Security</h2>
        <p>We take reasonable security measures to protect your personal information from:</p>
        <ul>
          <li>Unauthorized access</li>
          <li>Misuse</li>
          <li>Data loss</li>
        </ul>
        <p>However, no online platform can guarantee 100% security, and users should also take precautions when sharing information online.</p>

        <h2>5. Cookies</h2>
        <p>Our website may use cookies to enhance user experience. Cookies help us:</p>
        <ul>
          <li>Understand website traffic</li>
          <li>Improve performance</li>
          <li>Remember user preferences</li>
        </ul>
        <p>Users may choose to disable cookies through their browser settings.</p>

        <h2>6. Third-Party Links</h2>
        <p>
          Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of those external websites. Users are encouraged to review their privacy policies separately.
        </p>

        <h2>7. Changes to This Privacy Policy</h2>
        <p>
          Itanagar Choice reserves the right to update or modify this Privacy Policy at any time. Any changes will be posted on this page with an updated "Last Updated" date.
        </p>
      </div>

      {/* ── Shipping & Delivery Policy ──────────────────────────────────── */}
      <h1 className="font-display font-bold text-3xl text-gray-900 mt-16 mb-8">Shipping &amp; Delivery Policy (Digital Ticket Delivery)</h1>
      <div className="card p-6 sm:p-8 prose prose-sm max-w-none prose-headings:font-display prose-a:text-brand-600 prose-p:text-gray-600 prose-li:text-gray-600">
        <p>
          At Itanagar Choice, all tickets purchased through <strong>theitanagarchoice.com</strong> are delivered digitally. We do not provide any physical shipping of tickets.
        </p>

        <h2>1. Digital Ticket Delivery</h2>
        <p>Once a customer successfully completes the payment, the ticket will be automatically generated and delivered through the following methods:</p>
        <ul>
          <li>WhatsApp message to the registered mobile number</li>
          <li>Email to the registered email address</li>
          <li>Ticket may also be accessible through the user dashboard on the website</li>
        </ul>

        <h2>2. Delivery Time</h2>
        <p>Digital tickets are typically delivered <strong>instantly</strong> or within a few minutes after successful payment confirmation.</p>
        <p>In rare cases, delivery may take up to 24 hours due to technical or network issues.</p>

        <h2>3. Incorrect Contact Information</h2>
        <p>
          Customers are responsible for providing correct mobile numbers and email addresses during ticket purchase. Itanagar Choice is not responsible for delivery issues caused by incorrect information entered by the user.
        </p>

        <h2>4. Payment Confirmation</h2>
        <p>
          Tickets will only be generated after the payment gateway confirms a successful transaction. If the payment fails or remains pending, the ticket will not be issued.
        </p>

        <h2>5. Non-Delivery of Ticket</h2>
        <p>If you do not receive your ticket within the expected time:</p>
        <ul>
          <li>Check your email spam folder</li>
          <li>Check your WhatsApp messages</li>
          <li>Contact our support team</li>
        </ul>
        <p>Our team will assist you in retrieving your ticket.</p>
      </div>
    </div>
  );
}
