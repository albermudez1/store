function requireGatewayToken(req, res, next) {
  const gatewayToken = req.header("X-Gateway-Token");

  if (!gatewayToken) {
    return res.status(401).json({
      message: "Token de servicio no proporcionado.",
    });
  }

  if (gatewayToken !== process.env.GATEWAY_SERVICE_TOKEN) {
    return res.status(403).json({
      message: "Token de servicio inválido.",
    });
  }

  next();
}

module.exports = requireGatewayToken;