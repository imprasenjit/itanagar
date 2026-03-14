import { Routes, Route, Navigate } from 'react-router-dom';
import Navbar from './components/Navbar';
import Footer from './components/Footer';

// Public pages
import Home          from './pages/Home';
import Games         from './pages/Games';
import GameDetail    from './pages/GameDetail';
import Cart          from './pages/Cart';
import ConfirmOrder  from './pages/ConfirmOrder';
import PaymentSuccess from './pages/PaymentSuccess';
import Results       from './pages/Results';
import FAQ           from './pages/FAQ';
import Contact       from './pages/Contact';
import CmsPage       from './pages/CmsPage';
import Login         from './pages/Login';
import Register      from './pages/Register';
import ForgotPassword from './pages/ForgotPassword';

// Account pages
import Profile       from './pages/account/Profile';
import OrderHistory  from './pages/account/OrderHistory';
import Wallet        from './pages/account/Wallet';
import Winners       from './pages/account/Winners';
import Refunds       from './pages/account/Refunds';
import Withdrawals   from './pages/account/Withdrawals';
import Transfers     from './pages/account/Transfers';

function Layout({ children }) {
  return (
    <>
      <Navbar />
      <main>{children}</main>
      <Footer />
    </>
  );
}

function AuthLayout({ children }) {
  return <main className="min-h-screen">{children}</main>;
}

export default function App() {
  return (
    <Routes>
      {/* Auth — no Navbar/Footer */}
      <Route path="/login"           element={<AuthLayout><Login/></AuthLayout>}/>
      <Route path="/register"        element={<AuthLayout><Register/></AuthLayout>}/>
      <Route path="/forgot-password" element={<AuthLayout><ForgotPassword/></AuthLayout>}/>

      {/* Public — with Navbar/Footer */}
      <Route path="/"                element={<Layout><Home/></Layout>}/>
      <Route path="/games"           element={<Layout><Games/></Layout>}/>
      <Route path="/games/:id"       element={<Layout><GameDetail/></Layout>}/>
      <Route path="/cart"            element={<Layout><Cart/></Layout>}/>
      <Route path="/order/confirm"   element={<Layout><ConfirmOrder/></Layout>}/>
      <Route path="/payment/success" element={<Layout><PaymentSuccess/></Layout>}/>
      <Route path="/results"         element={<Layout><Results/></Layout>}/>
      <Route path="/faq"             element={<Layout><FAQ/></Layout>}/>
      <Route path="/contact"         element={<Layout><Contact/></Layout>}/>
      <Route path="/page/:type"      element={<Layout><CmsPage/></Layout>}/>

      {/* Account */}
      <Route path="/account/profile"     element={<Layout><Profile/></Layout>}/>
      <Route path="/account/orders"      element={<Layout><OrderHistory/></Layout>}/>
      <Route path="/account/wallet"      element={<Layout><Wallet/></Layout>}/>
      <Route path="/account/winners"     element={<Layout><Winners/></Layout>}/>
      <Route path="/account/refunds"     element={<Layout><Refunds/></Layout>}/>
      <Route path="/account/withdrawals" element={<Layout><Withdrawals/></Layout>}/>
      <Route path="/account/transfers"   element={<Layout><Transfers/></Layout>}/>

      {/* Fallback */}
      <Route path="*" element={<Navigate to="/" replace/>}/>
    </Routes>
  );
}

