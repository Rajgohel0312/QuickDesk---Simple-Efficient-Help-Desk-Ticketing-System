import { useEffect, useState } from "react";
import api from "../api/axios";
import { Link } from "react-router-dom";

function Dashboard() {
  const [tickets, setTickets] = useState([]);
  const [user, setUser] = useState(null);

  useEffect(() => {
    fetchUser();
    fetchTickets();
  }, []);

  const fetchUser = async () => {
    try {
      const res = await api.get("/me", { withCredentials: true });
      setUser(res.data);
    } catch (err) {
      console.error("User fetch error", err);
    }
  };

  const fetchTickets = async () => {
    try {
      const res = await api.get("/tickets");
      setTickets(res.data.data);
    } catch (err) {
      console.error("Tickets fetch error", err);
    }
  };

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">
        Welcome {user?.name} ({user?.role})
      </h1>

      <h2 className="text-xl font-semibold mb-2">Tickets</h2>

      <table className="w-full border">
        <thead>
          <tr className="bg-gray-100">
            <th className="p-2 border">Title</th>
            <th className="p-2 border">Status</th>
            <th className="p-2 border">Priority</th>
            <th className="p-2 border">Assigned To</th>
            <th className="p-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          {tickets?.map((ticket) => (
            <tr key={ticket.id}>
              <td className="p-2 border">
                <Link
                  to={`/ticket/${ticket.id}`}
                  className="text-blue-600 underline hover:text-blue-800"
                >
                  {ticket.subject}
                </Link>
              </td>
              <td className="p-2 border">{ticket.status}</td>
              <td className="p-2 border">{ticket.priority || "N/A"}</td>
              <td className="p-2 border">
                {ticket.assigned_to?.name || "Unassigned"}
              </td>
              <td className="p-2 border">
                <Link
                  to={`/add-comment/ticket/${ticket.id}`}
                  className="text-blue-600 underline hover:text-blue-800"
                >
                  Add Comment
                </Link>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default Dashboard;
