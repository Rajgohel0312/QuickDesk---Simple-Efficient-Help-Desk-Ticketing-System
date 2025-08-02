import { Routes, Route } from "react-router-dom";
import LoginPage from "./pages/Auth/LoginPage.jsx";
import RegisterPage from "./pages/Auth/Register";
import ProtectedRoute from "./context/ProtectedRoute.jsx";
import Dashboard from "./pages/Dashboard.jsx";
import TicketDetail from "./pages/TicketDetail.jsx";
import CreateTicket from "./pages/CreateTicket.jsx";
import AddComment from "./pages/AddComment.jsx";
import Logout from "./pages/Logout.jsx";
function App() {
  return (
    <Routes>
      <Route
        path="/dashboard"
        element={
          <ProtectedRoute>
            <Dashboard />
          </ProtectedRoute>
        }
      />
      <Route
        path="/tickets/create"
        element={
          <ProtectedRoute>
            <CreateTicket />
          </ProtectedRoute>
        }
      />
      {/* <Route path="/ticket/:id" element={<TicketDetail />} /> */}
      <Route path="/login" element={<LoginPage />} />
      <Route path="/ticket/:id" element={<TicketDetail />} />
      <Route path="/add-comment/ticket/:id" element={<AddComment />} />

      <Route path="/register" element={<RegisterPage />} />
      <Route path="/logout" element={<Logout />} />

    </Routes>
  );
}

export default App;
