// src/pages/Logout.jsx
import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

const Logout = () => {
  const navigate = useNavigate();

  useEffect(() => {
    // Clear token and any other auth-related data
    localStorage.removeItem("token");

    // Optionally show a toast or message here

    // Redirect to login
    navigate("/login");
  }, [navigate]);

  return null; // or a loading spinner if you want
};

export default Logout;
