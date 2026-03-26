import React from "react";

const ItanagarChoice = () => {
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      {/* Header */}
      <header className="flex items-center justify-between p-4 bg-white shadow">
        <div className="flex items-center space-x-2">
          <img
            src="/logo.png"
            alt="ITANAGARCHOICE"
            className="h-8 w-8"
          />
          <span className="font-bold text-lg">ITANAGARCHOICE</span>
        </div>
        <div className="flex space-x-4">
          <button className="text-gray-600">🔔</button>
          <button className="text-gray-600">👤</button>
        </div>
      </header>

      {/* Banner */}
      <section className="bg-blue-600 text-white text-center p-6">
        <h2 className="text-xl font-semibold">
          Book Tickets & Coupons for Verified Events
        </h2>
        <div className="flex justify-center space-x-6 mt-4">
          <span>✅ Safe</span>
          <span>🔒 Secure</span>
          <span>⚡ Instant</span>
        </div>
        <div className="flex justify-center space-x-4 mt-6">
          <button className="bg-white text-blue-600 px-4 py-2 rounded font-medium">
            Explore Events
          </button>
          <button className="bg-white text-blue-600 px-4 py-2 rounded font-medium">
            My Tickets
          </button>
        </div>
      </section>

      {/* How It Works */}
      <section className="p-6">
        <h3 className="text-lg font-bold mb-4">How It Works</h3>
        <div className="grid grid-cols-2 gap-4">
          <div className="bg-white p-4 rounded shadow">
            <h4 className="font-semibold">1. Browse Events</h4>
            <p className="text-sm text-gray-600">
              Explore upcoming events and draws.
            </p>
          </div>
          <div className="bg-white p-4 rounded shadow">
            <h4 className="font-semibold">2. Select Ticket / Coupon</h4>
            <p className="text-sm text-gray-600">Choose your option easily.</p>
          </div>
          <div className="bg-white p-4 rounded shadow">
            <h4 className="font-semibold">3. Make Payment</h4>
            <p className="text-sm text-gray-600">
              Quickly pay securely online.
            </p>
          </div>
          <div className="bg-white p-4 rounded shadow">
            <h4 className="font-semibold">4. Get Confirmation</h4>
            <p className="text-sm text-gray-600">
              Receive your e-tickets immediately.
            </p>
          </div>
        </div>
      </section>

      {/* Trending Events */}
      <section className="p-6">
        <h3 className="text-lg font-bold mb-4">Trending & Verified Events</h3>
        <div className="bg-white rounded shadow p-4">
          <h4 className="font-semibold">Kameng Campers</h4>
          <p className="text-sm text-gray-600">Draw #: 10</p>
          <p className="text-sm text-gray-600">Draw Date: 28 Apr 2026</p>
          <p className="text-sm text-red-500 font-medium">Status: Selling Fast</p>
          <div className="flex space-x-2 mt-4">
            <img
              src="/car1.jpg"
              alt="White SUV"
              className="h-20 w-28 object-cover rounded"
            />
            <img
              src="/car2.jpg"
              alt="Blue Car"
              className="h-20 w-28 object-cover rounded"
            />
            <img
              src="/car3.jpg"
              alt="Red Car"
              className="h-20 w-28 object-cover rounded"
            />
            <img
              src="/bike.jpg"
              alt="Blue Motorcycle"
              className="h-20 w-28 object-cover rounded"
            />
          </div>
          <button className="mt-4 w-full bg-blue-600 text-white py-2 rounded font-medium">
            Buy Ticket
          </button>
        </div>
      </section>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-0 right-0 bg-white shadow flex justify-around p-2">
        <button className="flex flex-col items-center text-blue-600">
          🏠 <span className="text-xs">Home</span>
        </button>
        <button className="flex flex-col items-center text-gray-600">
          🎟️ <span className="text-xs">Events</span>
        </button>
        <button className="flex flex-col items-center text-gray-600 relative">
          📄 <span className="text-xs">My Tickets</span>
          <span className="absolute top-0 right-2 bg-red-500 text-white text-xs rounded-full px-1">
            2
          </span>
        </button>
        <button className="flex flex-col items-center text-gray-600">
          👤 <span className="text-xs">Profile</span>
        </button>
        <button className="flex flex-col items-center text-gray-600">
          📊 <span className="text-xs">Results</span>
        </button>
      </nav>
    </div>
  );
};

export default ItanagarChoice