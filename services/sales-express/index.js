require("dotenv").config();

const express = require("express");
const cors = require("cors");

const connectDB = require("./config/db");
const salesRoutes = require("./routes/salesRoutes");

const requireGatewayToken = require("./middleware/requireGatewayToken");

const app = express();

connectDB();

app.use(cors());
app.use(express.json());

app.use("/api", salesRoutes);

app.get("/api/health", requireGatewayToken, (req, res) => {
  res.status(200).json({
    message: "Sales service is running.",
  });
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});