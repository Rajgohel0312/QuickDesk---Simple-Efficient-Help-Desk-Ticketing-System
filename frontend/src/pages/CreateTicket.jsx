import { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";

function CreateTicket() {
  const [subject, setSubject] = useState("");
  const [description, setDescription] = useState("");
  const [priority, setPriority] = useState("low");
  const [categoryId, setCategoryId] = useState("");
  const [attachment, setAttachment] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleCreate = async (e) => {
    e.preventDefault();
    setLoading(true);

    const formData = new FormData();
    formData.append("subject", subject);
    formData.append("description", description);
    formData.append("priority", priority);
    if (categoryId) formData.append("category_id", categoryId);
    if (attachment) formData.append("attachment", attachment);

    try {
      await api.post("tickets", formData, {
        withCredentials: true,
        headers: { "Content-Type": "multipart/form-data" },
      });
      alert("Ticket created successfully!");
      navigate("/dashboard");
    } catch (err) {
      alert("Something went wrong!");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-6 max-w-xl mx-auto">
      <h1 className="text-2xl font-bold mb-4">Create New Ticket</h1>
      <form onSubmit={handleCreate} className="space-y-4">
        <input
          type="text"
          placeholder="Subject"
          value={subject}
          onChange={(e) => setSubject(e.target.value)}
          className="w-full p-2 border rounded"
          required
        />

        <textarea
          placeholder="Describe your issue"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          className="w-full p-2 border rounded"
          rows="4"
          required
        />

        <select
          value={priority}
          onChange={(e) => setPriority(e.target.value)}
          className="w-full p-2 border rounded"
        >
          <option value="low">Low Priority</option>
          <option value="medium">Medium Priority</option>
          <option value="high">High Priority</option>
        </select>

        <input
          type="number"
          placeholder="Category ID (optional)"
          value={categoryId}
          onChange={(e) => setCategoryId(e.target.value)}
          className="w-full p-2 border rounded"
        />

        <input
          type="file"
          onChange={(e) => setAttachment(e.target.files[0])}
          className="w-full p-2 border rounded"
          accept=".jpg,.png,.pdf"
        />

        <button
          type="submit"
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          disabled={loading}
        >
          {loading ? "Creating..." : "Create Ticket"}
        </button>
      </form>
    </div>
  );
}

export default CreateTicket;
