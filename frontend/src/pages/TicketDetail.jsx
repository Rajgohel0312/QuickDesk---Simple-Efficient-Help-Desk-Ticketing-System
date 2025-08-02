import React, { useEffect, useState } from "react";
import api from "../api/axios";
import { useParams } from "react-router-dom";

const TicketDetail = () => {
  const { id } = useParams();
  const [ticket, setTicket] = useState(null);
  const [comments, setComments] = useState([]);
  const [status, setStatus] = useState("");
  const [userRole, setUserRole] = useState("");
  const [agents, setAgents] = useState([]);
  const [selectedAgent, setSelectedAgent] = useState("");
  const [newComment, setNewComment] = useState("");
  const [attachment, setAttachment] = useState(null);
  const [loading, setLoading] = useState(true);
  const [updatingStatus, setUpdatingStatus] = useState(false);
  const [assigningAgent, setAssigningAgent] = useState(false);
  const [commentSubmitting, setCommentSubmitting] = useState(false);

  const token = localStorage.getItem("token");

  useEffect(() => {
    const fetchAll = async () => {
      try {
        await Promise.all([
          fetchTicket(),
          fetchComments(),
          fetchUser(),
          fetchAgents(),
        ]);
      } catch (err) {
        console.error("Failed to load data", err);
      } finally {
        setLoading(false);
      }
    };
    fetchAll();
  }, []);

  const fetchTicket = async () => {
    const res = await api.get(`/tickets/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    setTicket(res.data);
    setStatus(res.data.status);
    setSelectedAgent(res.data.agent_id || "");
  };

  const fetchComments = async () => {
    const res = await api.get(`/tickets/${id}/comments`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    setComments(res.data);
  };

  const fetchUser = async () => {
    const res = await api.get("/me", {
      headers: { Authorization: `Bearer ${token}` },
    });
    setUserRole(res.data.role);
  };

  const fetchAgents = async () => {
    const res = await api.get("/agents", {
      headers: { Authorization: `Bearer ${token}` },
    });
    setAgents(res.data);
  };

  const handleStatusChange = async () => {
    setUpdatingStatus(true);
    try {
      await api.patch(
        `/tickets/${id}/status`,
        { status },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      alert("Status updated successfully");
      fetchTicket();
    } catch (error) {
      console.error("Status update failed", error);
      alert("Failed to update status");
    } finally {
      setUpdatingStatus(false);
    }
  };
  const handleAssignAgent = async () => {
    if (!selectedAgent) return alert("Please select an agent.");
    try {
      await api.post(
        `/tickets/${id}/update-status`,
        { assigned_to: selectedAgent },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      alert("Agent updated successfully");
      fetchTicket();
    } catch (error) {
      console.error("Error updating agent", error);
      alert("Failed to update agent");
    }
  };
  const handleCommentSubmit = async (e) => {
    e.preventDefault();
    setCommentSubmitting(true);
    const formData = new FormData();
    formData.append("content", newComment);
    if (attachment) formData.append("attachment", attachment);

    try {
      await api.post(`/tickets/${id}/comments`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "multipart/form-data",
        },
      });
      setNewComment("");
      setAttachment(null);
      await fetchComments();
    } catch (error) {
      console.error("Comment submission failed", error);
      alert("Failed to submit comment");
    } finally {
      setCommentSubmitting(false);
    }
  };

  if (loading) return <div className="p-4">Loading ticket details...</div>;
  if (!ticket) return <div className="p-4">Ticket not found.</div>;

  return (
    <div className="max-w-4xl mx-auto p-6">
      <h1 className="text-2xl font-bold mb-4">{ticket.title}</h1>
      <p className="mb-2 text-gray-700">{ticket.description}</p>
      <p className="text-sm text-gray-500 mb-2">
        Status: <span className="font-semibold">{ticket.status}</span>
      </p>
      <p className="text-sm text-gray-500 mb-4">
        Assigned to: {ticket.agent?.name || "Unassigned"}
      </p>

      {["admin", "agent"].includes(userRole) && (
        <>
          {/* Status Update */}
          <div className="mb-4">
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value)}
              className="border p-2 rounded"
            >
              <option value="Open">Open</option>
              <option value="In Progress">In Progress</option>
              <option value="Resolved">Resolved</option>
            </select>
            <button
              onClick={handleStatusChange}
              disabled={updatingStatus}
              className={`ml-2 px-4 py-2 rounded text-white ${
                updatingStatus ? "bg-blue-300" : "bg-blue-500"
              }`}
            >
              {updatingStatus ? "Updating..." : "Update Status"}
            </button>
          </div>

          {/* Assign Agent (Admin Only) */}
          {userRole === "admin" && (
            <div className="mb-4">
              <select
                className="form-select"
                value={selectedAgent || ""}
                onChange={(e) => setSelectedAgent(e.target.value)}
              >
                <option value="">-- Select Agent --</option>
                {agents.map((agent) => (
                  <option key={agent.id} value={agent.id}>
                    {agent.name}
                  </option>
                ))}
              </select>
              <button
                onClick={handleAssignAgent}
                disabled={assigningAgent}
                className={`ml-2 px-4 py-2 rounded text-white ${
                  assigningAgent ? "bg-green-300" : "bg-green-600"
                }`}
              >
                {assigningAgent ? "Assigning..." : "Assign Agent"}
              </button>
            </div>
          )}
        </>
      )}

      <hr className="my-6" />

      {/* Comments Section */}
      <h2 className="text-xl font-semibold mb-2">Comments</h2>
      <div className="space-y-4 mb-6">
        {comments.map((c, i) => (
          <div key={i} className="bg-gray-100 p-3 rounded">
            <p className="text-sm mb-2">{c.content}</p>
            {c.attachment && (
              <div className="mb-2">
                <a
                  href={`http://localhost:8000/storage/${c.attachment}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-blue-600 underline"
                >
                  View Attachment
                </a>
              </div>
            )}
            <p className="text-xs text-gray-500">
              By {c.user.name} at {new Date(c.created_at).toLocaleString()}
            </p>
          </div>
        ))}
      </div>

      {/* Add New Comment */}
      <form onSubmit={handleCommentSubmit} className="space-y-4">
        <textarea
          value={newComment}
          onChange={(e) => setNewComment(e.target.value)}
          className="w-full p-2 border rounded"
          placeholder="Add a comment..."
          required
        ></textarea>
        <input
          type="file"
          onChange={(e) => setAttachment(e.target.files[0])}
          className="block"
        />
        <button
          type="submit"
          disabled={commentSubmitting}
          className={`px-4 py-2 rounded text-white ${
            commentSubmitting ? "bg-blue-300" : "bg-blue-600"
          }`}
        >
          {commentSubmitting ? "Submitting..." : "Submit Comment"}
        </button>
      </form>
    </div>
  );
};

export default TicketDetail;
