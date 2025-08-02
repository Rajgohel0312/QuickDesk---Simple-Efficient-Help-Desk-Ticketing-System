import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import api from "../api/axios"; // make sure this is your axios instance with baseURL
function AddComment() {
  const { id } = useParams(); // Ticket ID from URL
  const [ticket, setTicket] = useState(null);
  const [comment, setComment] = useState("");
  const [file, setFile] = useState(null);
  const [statusMessage, setStatusMessage] = useState({ text: "", type: "" }); // Success/Error message

  const fetchTicket = async () => {
    try {
      const res = await api.get(`/tickets/${id}`, {
        withCredentials: true,
      });
      setTicket(res.data);
    } catch (err) {
      alert("Error fetching ticket");
    }
  };

  const handleCommentSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append("content", comment);
    if (file) formData.append("attachment", file);

    try {
      await api.post(`/tickets/${id}/comments`, formData, {
        withCredentials: true,
        headers: { "Content-Type": "multipart/form-data" },
      });

      setComment("");
      setFile(null);
      setStatusMessage({ text: "✅ Comment posted successfully!", type: "success" });
      fetchTicket(); // Refresh comments
    } catch (err) {
      console.error(err.response?.data || err.message);
      setStatusMessage({
        text: "❌ Failed to post comment. Try again later.",
        type: "error",
      });
    }

    // Remove message after 4 seconds
    setTimeout(() => {
      setStatusMessage({ text: "", type: "" });
    }, 4000);
  };

  useEffect(() => {
    fetchTicket();
  }, []);

  if (!ticket) return <p className="text-center mt-8">Loading ticket...</p>;

  return (
    <div className="max-w-3xl mx-auto p-6">
      <h1 className="text-2xl font-bold mb-2">{ticket.title}</h1>
      <p className="mb-4 text-gray-600">{ticket.message}</p>
      <p className="text-sm text-gray-500 mb-4">
        Priority: {ticket.priority} | Status: {ticket.status}
      </p>

      <hr className="my-6" />

      <h2 className="text-lg font-semibold mb-2">Comments</h2>
      <div className="space-y-4 mb-6">
        {ticket.comments?.length === 0 && (
          <p className="text-gray-500">No comments yet.</p>
        )}
        {ticket.comments?.map((c) => (
          <div key={c.id} className="border p-3 rounded shadow-sm bg-white">
            <p className="text-gray-800">{c.message}</p>
            {c.attachment_url && (
              <div className="mt-1">
                <a
                  href={c.attachment_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-blue-500 underline text-sm"
                >
                  📎 View Attachment
                </a>
              </div>
            )}
            <p className="text-xs text-gray-500 mt-1">
              {c.user?.name} — {new Date(c.created_at).toLocaleString()}
            </p>
          </div>
        ))}
      </div>

      {/* Status Message */}
      {statusMessage.text && (
        <div
          className={`p-3 rounded mb-4 ${
            statusMessage.type === "success"
              ? "bg-green-100 text-green-700"
              : "bg-red-100 text-red-700"
          }`}
        >
          {statusMessage.text}
        </div>
      )}

      <form onSubmit={handleCommentSubmit} className="space-y-4">
        <textarea
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          className="w-full border rounded p-2"
          placeholder="Write your comment..."
          rows={3}
          required
        />
        <input
          type="file"
          onChange={(e) => setFile(e.target.files[0])}
          className="block"
        />
        <button
          type="submit"
          className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
        >
          Post Comment
        </button>
      </form>
    </div>
  );
}

export default AddComment;
